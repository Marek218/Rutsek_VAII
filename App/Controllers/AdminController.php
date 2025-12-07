<?php

namespace App\Controllers;

use App\Models\Order;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Class AdminController
 *
 * This controller manages admin-related actions within the application.It extends the base controller functionality
 * provided by BaseController.
 *
 * @package App\Controllers
 */
class AdminController extends BaseController
{
    /**
     * Authorizes actions in this controller.
     *
     * This method checks if the user is logged in, allowing or denying access to specific actions based
     * on the authentication state.
     *
     * @param string $action The name of the action to authorize.
     * @return bool Returns true if the user is logged in; false otherwise.
     */
    public function authorize(Request $request, string $action): bool
    {
        return $this->user->isLoggedIn();
    }

    /**
     * Displays the index page of the admin panel.
     * Shows list of reservations (orders).
     */
    public function index(Request $request): Response
    {
        // Load all orders, newest first
        $orders = Order::getAll(orderBy: '`created_at` DESC');
        return $this->html(compact('orders'));
    }

    /**
     * Edit reservation by id. GET shows form, POST updates.
     */
    public function edit(Request $request): Response
    {
        $id = (int)($request->value('id') ?? 0);
        if ($id <= 0) {
            return $this->redirect($this->url('admin.index'));
        }

        $order = Order::getOne($id);
        if (!$order) {
            return $this->redirect($this->url('admin.index'));
        }

        if ($request->isPost()) {
            // Minimal validation and normalization
            $order->first_name = trim((string)$request->value('first_name')) ?: $order->first_name;
            $order->last_name  = trim((string)$request->value('last_name')) ?: $order->last_name;
            $email = trim((string)$request->value('email'));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $order->email = $email;
            }
            $order->phone   = trim((string)$request->value('phone')) ?: $order->phone;
            $order->service = trim((string)$request->value('service')) ?: $order->service;
            $date = trim((string)$request->value('date'));
            if ($date !== '') { $order->date = $date; }
            $time = trim((string)$request->value('time'));
            if ($time !== '') {
                if (preg_match('/^\d{2}:\d{2}$/', $time)) { $time .= ':00'; }
                $order->time = $time;
            }
            $notes = $request->value('notes');
            $order->notes = ($notes === '' ? null : $notes);

            $order->save();
            return $this->redirect($this->url('admin.index'));
        }

        return $this->html(['order' => $order], 'edit');
    }

    /**
     * Delete reservation by id (POST only).
     */
    public function delete(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('admin.index'));
        }
        $id = (int)($request->value('id') ?? 0);
        if ($id > 0) {
            $order = Order::getOne($id);
            if ($order) {
                $order->delete();
            }
        }
        return $this->redirect($this->url('admin.index'));
    }
}
