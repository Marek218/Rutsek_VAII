<?php

namespace App\Controllers;

use App\Models\Order;
use App\Models\Service;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\JsonResponse;
use Framework\Http\Responses\Response;

class OrderController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    // Shows the booking form view
    public function index(Request $request): Response
    {
        // Load services for select (1:N relationship: service -> many orders)
        $services = Service::getAll(orderBy: '`name` ASC');
        return $this->html(compact('services'));
    }

    /**
     * Returns true if requested slot [start, start+duration) overlaps any existing reservation on the same date.
     */
    private function hasOverlap(string $date, string $startTime, int $durationMinutes = 60): bool
    {
        // Normalize to HH:MM:SS
        $startTime = preg_match('/^\d{2}:\d{2}:\d{2}$/', $startTime) ? $startTime : ($startTime . ':00');

        $startTs = strtotime($date . ' ' . $startTime);
        if ($startTs === false) return true;
        $endTs = $startTs + ($durationMinutes * 60);

        $orders = Order::getAll('`date` = ?', [$date]);
        foreach ($orders as $o) {
            $t = (string)($o->time ?? '');
            if ($t === '') continue;
            // assume stored as HH:MM:SS
            $existingStart = strtotime($date . ' ' . $t);
            if ($existingStart === false) continue;
            $existingEnd = $existingStart + ($durationMinutes * 60);

            // overlap if intervals intersect
            if ($startTs < $existingEnd && $endTs > $existingStart) {
                return true;
            }
        }

        return false;
    }

    /**
     * AJAX: checks whether given slot (date+time) is available.
     * All services are treated as 60-minute blocks.
     * GET /order/availability?date=YYYY-MM-DD&time=HH:MM
     */
    public function availability(Request $request): Response
    {
        $date = trim((string)$request->value('date'));
        $time = trim((string)$request->value('time'));

        $errors = [];
        if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors['date'] = 'Neplatný dátum.';
        }
        if ($time === '' || !preg_match('/^\d{2}:\d{2}$/', $time)) {
            $errors['time'] = 'Neplatný čas.';
        }

        if (!empty($errors)) {
            return new JsonResponse(['ok' => false, 'available' => false, 'errors' => $errors], 400);
        }

        // Past date guard
        $today = date('Y-m-d');
        if ($date < $today) {
            return new JsonResponse(['ok' => true, 'available' => false, 'reason' => 'Termín je v minulosti.'], 200);
        }

        $timeDb = $time . ':00';
        $available = !$this->hasOverlap($date, $timeDb, 60);

        return new JsonResponse([
            'ok' => true,
            'available' => $available,
            'reason' => $available ? null : 'Tento termín je už obsadený.'
        ]);
    }

    // Handles booking form submission

    /**
     * @throws \Exception
     */
    public function submit(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('order.index'));
        }

        $first = trim((string)$request->value('first_name'));
        $last = trim((string)$request->value('last_name'));
        $email = trim((string)$request->value('email'));
        $phone = trim((string)$request->value('phone'));
        $serviceId = (int)($request->value('service_id') ?? 0);
        $date = trim((string)$request->value('date'));
        $time = trim((string)$request->value('time'));
        $notes = trim((string)$request->value('notes'));

        $errors = [];
        if ($first === '') { $errors['first_name'] = 'Meno je povinné.'; }
        if ($last === '') { $errors['last_name'] = 'Priezvisko je povinné.'; }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Neplatný email.'; }
        if ($phone === '' || !preg_match('/^[+0-9 ()-]{6,20}$/', $phone)) { $errors['phone'] = 'Neplatný telefón.'; }
        if ($serviceId <= 0) { $errors['service_id'] = 'Vyberte službu.'; }
        if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { $errors['date'] = 'Neplatný dátum.'; }
        if ($time === '' || !preg_match('/^\d{2}:\d{2}$/', $time)) { $errors['time'] = 'Neplatný čas.'; }

        // Past date guard
        $today = date('Y-m-d');
        if ($date !== '' && $date < $today) {
            $errors['date'] = 'Dátum nemôže byť v minulosti.';
        }

        $service = null;
        if ($serviceId > 0) {
            $service = Service::getOne($serviceId);
            if (!$service) {
                $errors['service_id'] = 'Vyberte platnú službu.';
            }
        }

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return new JsonResponse(['ok' => false, 'errors' => $errors], 422);
            }
            // Non-AJAX: re-render view with errors and old input
            $services = Service::getAll(orderBy: '`name` ASC');
            return $this->html(['errors' => $errors, 'old' => $request->post(), 'services' => $services], 'Home/order');
        }

        $timeDb = $time . ':00';

        // Collision check: 60-min block overlap on same date
        if ($this->hasOverlap($date, $timeDb, 60)) {
            $errors['time'] = 'Tento termín je už obsadený. Vyberte iný.';
            if ($request->isAjax()) {
                return new JsonResponse(['ok' => false, 'errors' => $errors], 422);
            }
            $services = Service::getAll(orderBy: '`name` ASC');
            return $this->html(['errors' => $errors, 'old' => $request->post(), 'services' => $services], 'Home/order');
        }

        $order = new Order();
        $order->first_name = $first;
        $order->last_name = $last;
        $order->email = $email;
        $order->phone = $phone;
        $order->service_id = $serviceId;
        // keep legacy `service` column filled with human readable name for old views
        $order->service = (string)($service->name ?? '');
        $order->date = $date;
        $order->time = $timeDb;
        $order->notes = $notes ?: null;

        try {
            $order->save();
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
            }
            $services = Service::getAll(orderBy: '`name` ASC');
            return $this->html(['error' => $e->getMessage(), 'old' => $request->post(), 'services' => $services], 'Home/order');
        }

        if ($request->isAjax()) {
            return new JsonResponse(['ok' => true, 'id' => $order->id]);
        }

        return $this->redirect($this->url('home.index'));
    }

    /**
     * AJAX: returns the nearest available 60-min slot starting from given date+time.
     * GET /order/nextAvailable?date=YYYY-MM-DD&time=HH:MM
     */
    public function nextAvailable(Request $request): Response
    {
        $date = trim((string)$request->value('date'));
        $time = trim((string)$request->value('time'));

        $errors = [];
        if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors['date'] = 'Neplatný dátum.';
        }
        if ($time === '' || !preg_match('/^\d{2}:\d{2}$/', $time)) {
            $errors['time'] = 'Neplatný čas.';
        }
        if (!empty($errors)) {
            return new JsonResponse(['ok' => false, 'errors' => $errors], 400);
        }

        $startTs = strtotime($date . ' ' . $time . ':00');
        if ($startTs === false) {
            return new JsonResponse(['ok' => false, 'error' => 'Neplatný dátum/čas.'], 400);
        }

        // Search forward in 60-minute steps, up to 30 days
        $limitSteps = 24 * 30;
        $ts = $startTs;
        for ($i = 0; $i < $limitSteps; $i++) {
            $d = date('Y-m-d', $ts);
            $t = date('H:i:s', $ts);
            if (!$this->hasOverlap($d, $t, 60)) {
                return new JsonResponse([
                    'ok' => true,
                    'date' => $d,
                    'time' => substr($t, 0, 5)
                ]);
            }
            $ts += 3600;
        }

        return new JsonResponse(['ok' => true, 'date' => null, 'time' => null, 'reason' => 'V najbližších 30 dňoch sa nenašiel voľný termín.']);
    }
}
