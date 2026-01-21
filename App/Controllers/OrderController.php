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
        $services = [];
        try {
            $services = Service::getAll(orderBy: '`name` ASC');
        } catch (\Throwable $e) {
            $services = [];
        }
        // Render the existing Home/order view
        return $this->html(compact('services'), 'Home/order');
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

        try {
            $orders = Order::getAll('`date` = ?', [$date]);
        } catch (\Throwable $e) {
            // On DB error, treat as overlapping to be safe
            return true;
        }

        foreach ($orders as $o) {
            $t = (string)($o->time ?? '');
            if ($t === '') continue;
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

        try {
            $available = !$this->hasOverlap($date, $timeDb, 60);
        } catch (\Throwable $e) {
            return new JsonResponse(['ok' => false, 'available' => false, 'error' => 'Chyba pri overovaní dostupnosti.'], 500);
        }

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

        try {
            if ($this->hasOverlap($date, $timeDb, 60)) {
                $errors['time'] = 'Tento termín je už obsadený. Vyberte iný.';
                if ($request->isAjax()) {
                    return new JsonResponse(['ok' => false, 'errors' => $errors], 422);
                }
                $services = Service::getAll(orderBy: '`name` ASC');
                return $this->html(['errors' => $errors, 'old' => $request->post(), 'services' => $services], 'Home/order');
            }
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return new JsonResponse(['ok' => false, 'error' => 'Chyba pri overovaní dostupnosti: ' . $e->getMessage()], 500);
            }
            $services = Service::getAll(orderBy: '`name` ASC');
            return $this->html(['error' => 'Chyba pri overovaní dostupnosti.', 'old' => $request->post(), 'services' => $services], 'Home/order');
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

        try {
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
        } catch (\Throwable $e) {
            return new JsonResponse(['ok' => false, 'error' => 'Chyba pri vyhľadávaní voľného termínu.'], 500);
        }

        return new JsonResponse(['ok' => true, 'date' => null, 'time' => null, 'reason' => 'V najbližších 30 dňoch sa nenašiel voľný termín.']);
    }

    // Diagnostic endpoint for DB/schema debugging (safe for local dev)
    public function diag(Request $request): Response
    {
        if (!$this->user->isLoggedIn()) {
            // restrict detailed diag to admins
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $ordersCols = Order::executeRawSQL('DESCRIBE orders');
        } catch (\Throwable $e) {
            $ordersCols = ['error' => $e->getMessage()];
        }

        try {
            $servicesCols = Service::executeRawSQL('DESCRIBE services');
        } catch (\Throwable $e) {
            $servicesCols = ['error' => $e->getMessage()];
        }

        return $this->json(['ok' => true, 'orders' => $ordersCols, 'services' => $servicesCols]);
    }

    // Admin CRUD: list orders
    public function adminList(Request $request): Response
    {
        // require admin (simple check)
        if (!$this->user->isLoggedIn()) {
            return $this->redirect($this->url('auth.login'));
        }

        $page = max(1, (int)($request->value('page') ?? 1));
        $perPage = (int)($request->value('per_page') ?? 15);
        if ($perPage <= 0) $perPage = 15;
        if ($perPage > 100) $perPage = 100;

        try {
            $total = Order::getCount();
        } catch (\Throwable $e) {
            $total = 0;
        }
        $offset = ($page - 1) * $perPage;
        try {
            $orders = Order::getAll(null, [], '`created_at` DESC', $perPage, $offset);
        } catch (\Throwable $e) {
            $orders = [];
        }

        $totalPages = $perPage > 0 ? max(1, (int)ceil($total / $perPage)) : 1;

        return $this->html(compact('orders', 'page', 'perPage', 'total', 'totalPages'), 'Admin/order');
    }

    // Admin edit (GET shows form, POST saves)
    public function edit(Request $request): Response
    {
        if (!$this->user->isLoggedIn()) {
            return $this->redirect($this->url('auth.login'));
        }

        $id = (int)($request->value('id') ?? 0);
        if ($id <= 0) {
            return $this->redirect($this->url('admin.orders'));
        }

        $order = Order::getOne($id);
        if (!$order) {
            return $this->redirect($this->url('admin.orders'));
        }

        $services = Service::getAll(orderBy: '`name` ASC');

        if ($request->isPost()) {
            // Debug logging: record that edit POST arrived and some key params
            try { error_log('[DEBUG] OrderController::edit POST id=' . $id . ' ajax=' . ($request->isAjax() ? '1' : '0') . ' data=' . json_encode($request->post())); } catch (\Throwable $e) {}

            $order->first_name = trim((string)$request->value('first_name')) ?: $order->first_name;
            $order->last_name = trim((string)$request->value('last_name')) ?: $order->last_name;
            $email = trim((string)$request->value('email'));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $order->email = $email;
            }
            $order->phone = trim((string)$request->value('phone')) ?: $order->phone;

            $serviceId = (int)($request->value('service_id') ?? 0);
            if ($serviceId > 0) {
                $svc = Service::getOne($serviceId);
                if ($svc) {
                    $order->service_id = $serviceId;
                    $order->service = (string)($svc->name ?? $order->service);
                }
            }

            $date = trim((string)$request->value('date'));
            if ($date !== '') {
                $order->date = $date;
            }
            $time = trim((string)$request->value('time'));
            if ($time !== '') {
                if (preg_match('/^\d{2}:\d{2}$/', $time)) {
                    $time .= ':00';
                }
                $order->time = $time;
            }
            $notes = $request->value('notes');
            $order->notes = ($notes === '' ? null : $notes);

            $order->save();
            if ($request->isAjax() || $request->wantsJson()) {
                return $this->json(['ok' => true, 'id' => $order->id]);
            }
            return $this->redirect($this->url('admin.orders'));
        }

        return $this->html(['order' => $order, 'services' => $services], 'Admin/edit');
    }

    // Admin update alias (CRUD-style): keep update() as alias to edit() POST handling
    public function update(Request $request): Response
    {
        return $this->edit($request);
    }

    // Admin delete
    public function delete(Request $request): Response
    {
        try { error_log('[DEBUG] OrderController::delete called method=' . ($request->isPost() ? 'POST' : 'GET') . ' ajax=' . ($request->isAjax() ? '1' : '0') . ' params=' . json_encode($request->post() ?: $request->get())); } catch (\Throwable $e) {}
        if (!$this->user->isLoggedIn()) {
            return $this->redirect($this->url('auth.login'));
        }
        if (!$request->isPost()) {
            return $this->redirect($this->url('admin.orders'));
        }

        $id = (int)($request->value('id') ?? 0);
        if ($id > 0) {
            $order = Order::getOne($id);
            if ($order) {
                $order->delete();
            }
        }

        if ($request->isAjax() || $request->wantsJson()) {
            return $this->json(['ok' => true, 'id' => $id]);
        }

        return $this->redirect($this->url('admin.orders'));
    }
}
