<?php

namespace App\Controllers;

use App\Models\Order;
use App\Models\ContactMessage;
use App\Models\ContactInfo;
use App\Models\HomeBox;
use App\Models\Service;
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
     * Admin dashboard (simple landing page).
     */
    public function index(Request $request): Response
    {
        return $this->html();
    }

    /**
     * Admin: list reservations (orders).
     */
    public function orders(Request $request): Response
    {
        // Delegate to OrderController::adminList for admin reservation list
        return $this->redirect($this->url('order.adminList'));
    }

    /**
     * Admin: list contact form messages.
     * Delegate to central MessageController to avoid duplicate logic.
     */
    public function messages(Request $request): Response
    {
        $messages = ContactMessage::getAll(orderBy: '`created_at` DESC');
        return $this->html(compact('messages'));
    }

    /**
     * Admin: delete one contact message (POST only).
     */
    public function deleteMessage(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('admin.messages'));
        }

        $id = (int)($request->value('id') ?? 0);
        if ($id > 0) {
            $msg = ContactMessage::getOne($id);
            if ($msg) {
                $msg->delete();
            }
        }

        if ($request->isAjax() || $request->wantsJson()) {
            return new \Framework\Http\Responses\JsonResponse(['ok' => true, 'id' => $id]);
        }

        return $this->redirect($this->url('admin.messages'));
    }

    /**
     * Edit reservation by id. GET shows form, POST updates.
     */
    public function edit(Request $request): Response
    {
        // Delegate to OrderController::edit
        return $this->redirect($this->url('order.edit', ['id' => (int)($request->value('id') ?? 0)]));
    }

    /**
     * Delete reservation by id (POST only).
     */
    public function delete(Request $request): Response
    {
        // Delegate to OrderController::delete
        return $this->redirect($this->url('order.delete', ['id' => (int)($request->value('id') ?? 0)]));
    }

    /**
     * Admin: edit contact page settings (phone, address, map...).
     */
    public function contact(Request $request): Response
    {
        return $this->redirect($this->url('contactInfo.edit'));
    }

    /**
     * Admin: edit homepage boxes (titles/descriptions shown on Home page).
     */
    public function homeBoxes(Request $request): Response
    {
        return $this->redirect($this->url('homeBox.index'));
    }
}

