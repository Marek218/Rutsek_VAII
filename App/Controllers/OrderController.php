<?php

namespace App\Controllers;

use App\Models\Order;
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
        return $this->html();
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
        $service = trim((string)$request->value('service'));
        $date = trim((string)$request->value('date'));
        $time = trim((string)$request->value('time'));
        $notes = trim((string)$request->value('notes'));

        $errors = [];
        if ($first === '') { $errors['first_name'] = 'Meno je povinné.'; }
        if ($last === '') { $errors['last_name'] = 'Priezvisko je povinné.'; }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Neplatný email.'; }
        if ($phone === '') { $errors['phone'] = 'Telefón je povinný.'; }
        if ($service === '') { $errors['service'] = 'Vyberte službu.'; }
        if ($date === '') { $errors['date'] = 'Dátum je povinný.'; }
        if ($time === '') { $errors['time'] = 'Čas je povinný.'; }

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return new JsonResponse(['ok' => false, 'errors' => $errors], 422);
            }
            return $this->redirect($this->url('order.index', ['error' => 'validation']));
        }

        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            $time .= ':00';
        }

        $order = new Order();
        $order->first_name = $first;
        $order->last_name = $last;
        $order->email = $email;
        $order->phone = $phone;
        $order->service = $service;
        $order->date = $date;
        $order->time = $time;
        $order->notes = $notes ?: null;
        $order->save();

        if ($request->isAjax()) {
            return new JsonResponse(['ok' => true, 'id' => $order->id]);
        }

        return $this->redirect($this->url('home.index', ['order' => 'ok']));
    }
}

