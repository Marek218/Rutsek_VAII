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

        $errors = [];
        if ($data['website'] !== '') { // honeypot
            $errors['form'] = 'Invalid submission';
        }
        if ($data['name'] === '') { $errors['name'] = 'Meno je povinné'; }
        if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Zadajte platný email'; }
        if (strlen($data['message']) < 5) { $errors['message'] = 'Správa musí mať aspoň 5 znakov'; }

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return new JsonResponse(['ok' => false, 'errors' => $errors], 422);
            }
            // show back on contact page with errors
            return $this->html(['errors' => $errors, 'old' => $data]);
        }

        $id = ContactMessage::create($data);

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
