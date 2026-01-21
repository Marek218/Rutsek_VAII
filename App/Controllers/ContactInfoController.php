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
        $contact = ContactInfo::getOne(1);
        if (!$contact) {
            $contact = new ContactInfo();
            $contact->id = 1;
            $contact->salon_name = 'Kaderníctvo Luxer';
            $contact->person_name = null;
            $contact->phone = '';
            $contact->email = '';
            $contact->address_line = '';
            $contact->opening_hours = null;
            $contact->map_embed_url = null;
            try { $contact->save(); } catch (\Throwable $e) { /* ignore */ }
        }

        $errors = [];

        if ($request->isPost()) {
            $salonName = trim((string)$request->value('salon_name'));
            $personName = trim((string)$request->value('person_name'));
            $phone = trim((string)$request->value('phone'));
            $email = trim((string)$request->value('email'));
            $address = trim((string)$request->value('address_line'));
            $opening = trim((string)$request->value('opening_hours'));
            $mapUrl = trim((string)$request->value('map_embed_url'));

            if ($salonName === '' || mb_strlen($salonName) < 2) {
                $errors['salon_name'] = 'Názov salónu musí mať aspoň 2 znaky.';
            }
            if ($phone === '' || mb_strlen($phone) < 6) {
                $errors['phone'] = 'Telefón je povinný (min. 6 znakov).';
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Zadajte platný email.';
            }
            if ($address === '' || mb_strlen($address) < 5) {
                $errors['address_line'] = 'Adresa je povinná (min. 5 znakov).';
            }

            if (empty($errors)) {
                try {
                    $contact->salon_name = $salonName;
                    $contact->person_name = ($personName === '' ? null : $personName);
                    $contact->phone = $phone;
                    $contact->email = $email;
                    $contact->address_line = $address;
                    $contact->opening_hours = ($opening === '' ? null : $opening);
                    $contact->map_embed_url = ($mapUrl === '' ? null : $mapUrl);
                    $contact->save();
                    return $this->redirect($this->url('admin.contact'));
                } catch (\Throwable $e) {
                    $errors['form'] = 'Uloženie zlyhalo. Skúste to prosím znova.';
                }
            } else {
                // keep entered values in object
                $contact->salon_name = $salonName;
                $contact->person_name = ($personName === '' ? null : $personName);
                $contact->phone = $phone;
                $contact->email = $email;
                $contact->address_line = $address;
                $contact->opening_hours = ($opening === '' ? null : $opening);
                $contact->map_embed_url = ($mapUrl === '' ? null : $mapUrl);
            }
        }

        return $this->html(['contact' => $contact, 'errors' => $errors], 'Admin/contact');
    }
}
