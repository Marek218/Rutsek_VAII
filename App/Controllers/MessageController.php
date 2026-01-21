<?php

namespace App\Controllers;

use App\Models\ContactMessage;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\JsonResponse;
use Framework\Http\Responses\RedirectResponse;
use Framework\Http\Responses\Response;

class MessageController extends BaseController
{
    // Show admin list of messages
    public function index(Request $request): Response
    {
        if (!$this->user->isLoggedIn()) {
            return $this->redirect($this->url('admin.index'));
        }

        $messages = ContactMessage::all();
        return $this->html(compact('messages'));
    }

    // Store public contact form (accepts normal POST and AJAX)
    public function store(Request $request): Response
    {
        $data = [
            'name' => trim($request->value('name', '')),
            'email' => trim($request->value('email', '')),
            'phone' => trim($request->value('phone', '')),
            'subject' => trim($request->value('subject', '')),
            'message' => trim($request->value('message', '')),
            'website' => trim($request->value('website', '')),
            'ip' => (string)($request->server('REMOTE_ADDR') ?? ''),
            'user_agent' => (string)($request->server('HTTP_USER_AGENT') ?? ''),
        ];

        try {
            $id = ContactMessage::createFromArray($data);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $decoded = json_decode($msg, true);
            if (is_array($decoded)) {
                if ($request->isAjax()) {
                    return new JsonResponse(['ok' => false, 'errors' => $decoded], 422);
                }
                return $this->html(['errors' => $decoded, 'old' => $data]);
            }
            if ($request->isAjax()) {
                return new JsonResponse(['ok' => false, 'error' => $msg], 500);
            }
            return $this->html(['error' => $msg, 'old' => $data]);
        }

        if ($request->isAjax()) {
            return new JsonResponse(['ok' => true, 'id' => $id]);
        }

        // redirect back to contact with flash
        return $this->redirect($this->url('home.contact', ['flash' => 'sent']));
    }

    public function destroy(Request $request, int $id): Response
    {
        if (!$this->user->isLoggedIn()) {
            return $this->redirect($this->url('admin.index'));
        }

        $msg = ContactMessage::getOne($id);
        if ($msg) { $msg->delete(); }
        return $this->redirect($this->url('admin.messages'));
    }
}