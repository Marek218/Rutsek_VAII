<?php

namespace App\Controllers;

use App\Models\ContactMessage;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\JsonResponse;
use Framework\Http\Responses\RedirectResponse;
use Framework\Http\Responses\Response;
use App\Configuration;

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
            'name' => trim((string)($request->value('name') ?? '')),
            'email' => trim((string)($request->value('email') ?? '')),
            'phone' => trim((string)($request->value('phone') ?? '')),
            'subject' => trim((string)($request->value('subject') ?? '')),
            'message' => trim((string)($request->value('message') ?? '')),
            'website' => trim((string)($request->value('website') ?? '')),
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
                return $this->html(['errors' => $decoded, 'old' => $data], 'Home/contact');
            }

            // Unexpected exception: log details and return a friendly error message
            try {
                $root = dirname(__DIR__, 2);
                $logDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log';
                if (!is_dir($logDir)) {
                    @mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . DIRECTORY_SEPARATOR . 'contact_errors.log';
                $entry = '[' . date('Y-m-d H:i:s') . '] ' . get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n";
                @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
            } catch (\Throwable $_) {
                // ignore logging failures
            }

            // If debugging is enabled, return the exception details to help diagnose the 500 error.
            if (defined('\App\Configuration::SHOW_EXCEPTION_DETAILS') && \App\Configuration::SHOW_EXCEPTION_DETAILS) {
                if ($request->isAjax()) {
                    return new JsonResponse(['ok' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
                }
                // render a minimal debug response
                $html = '<h2>Debug: unexpected exception</h2><pre>' . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                return $this->html(['error' => $html, 'old' => $data], 'Home/contact');
            }

            $userMsg = 'Pri odosielaní správy nastala neočakávaná chyba. Skúste to, prosím, neskôr.';

            if ($request->isAjax()) {
                return new JsonResponse(['ok' => false, 'error' => $userMsg], 500);
            }
            return $this->html(['error' => $userMsg, 'old' => $data], 'Home/contact');
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
