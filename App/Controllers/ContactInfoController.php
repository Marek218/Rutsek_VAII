<?php

namespace App\Controllers;

use App\Models\ContactInfo;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class ContactInfoController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        // require login for all admin contact actions
        return $this->user->isLoggedIn();
    }

    public function index(Request $request): Response
    {
        return $this->edit($request);
    }

    public function edit(Request $request): Response
    {
        try { error_log('[DEBUG] ContactInfoController::edit called method=' . ($request->isPost() ? 'POST' : 'GET') . ' ajax=' . ($request->isAjax() ? '1' : '0') . ' params=' . json_encode($request->post() ?: $request->get())); } catch (\Throwable $e) {}
        // ensure row exists
        $contact = ContactInfo::getOne(1) ?? new ContactInfo();

        if ($request->isPost()) {
            try {
                $data = [
                    'salon_name' => $request->value('salon_name'),
                    'person_name' => $request->value('person_name'),
                    'phone' => $request->value('phone'),
                    'email' => $request->value('email'),
                    'address_line' => $request->value('address_line'),
                    'opening_hours' => $request->value('opening_hours'),
                    'map_embed_url' => $request->value('map_embed_url'),
                ];
                $contact = ContactInfo::saveFromArray($data);
                return $this->redirect($this->url('admin.contact'));
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                $decoded = json_decode($msg, true);
                $errors = is_array($decoded) ? $decoded : ['form' => 'Uloženie zlyhalo. Skúste to prosím znova.'];
                // ensure $contact contains submitted values for re-render
                foreach ($data as $k => $v) { if (property_exists($contact, $k)) $contact->{$k} = $v; }
                return $this->html(['contact' => $contact, 'errors' => $errors], 'Admin/contact');
            }
        }

        return $this->html(['contact' => $contact, 'errors' => []], 'Admin/contact');
    }
}

