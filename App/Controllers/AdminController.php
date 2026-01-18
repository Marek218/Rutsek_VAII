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
        $orders = Order::getAll(orderBy: '`created_at` DESC');
        return $this->html(compact('orders'), 'order');
    }

    /**
     * Admin: list contact form messages.
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
     * Admin: list editable page texts (CMS).
     * Feature removed by request.
     */
    public function pages(Request $request): Response
    {
        return $this->redirect($this->url('admin.index'));
    }

    /**
     * Admin: edit a single page text row.
     * Feature removed by request.
     */
    public function editPage(Request $request): Response
    {
        return $this->redirect($this->url('admin.index'));
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

        $services = Service::getAll(orderBy: '`name` ASC');

        if ($request->isPost()) {
            // Minimal validation and normalization
            $order->first_name = trim((string)$request->value('first_name')) ?: $order->first_name;
            $order->last_name  = trim((string)$request->value('last_name')) ?: $order->last_name;
            $email = trim((string)$request->value('email'));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $order->email = $email;
            }
            $order->phone   = trim((string)$request->value('phone')) ?: $order->phone;

            $serviceId = (int)($request->value('service_id') ?? 0);
            if ($serviceId > 0) {
                $svc = Service::getOne($serviceId);
                if ($svc) {
                    $order->service_id = $serviceId;
                    // keep legacy service text in sync
                    $order->service = (string)($svc->name ?? $order->service);
                }
            }

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

        return $this->html(['order' => $order, 'services' => $services], 'edit');
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

    /**
     * Admin: edit contact page settings (phone, address, map...).
     */
    public function contact(Request $request): Response
    {
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
            // logo is fixed asset now; no DB field
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

        return $this->html(['contact' => $contact, 'errors' => $errors], 'contact');
    }

    /**
     * Admin: edit homepage boxes (titles/descriptions shown on Home page).
     */
    public function homeBoxes(Request $request): Response
    {
        $errors = [];

        // Ensure defaults exist (best-effort)
        try {
            $existing = HomeBox::getAll();
            if (empty($existing)) {
                $seed = [
                    ['damske', 'Dámske strihy', 'Od klasických po moderné účesy', 10],
                    ['panske', 'Pánske strihy', 'Presné a štylizované strihy', 20],
                    ['farbenie', 'Farbenie', 'Profesionálne farbenie vlasov', 30],
                    ['trvala', 'Trvalá', 'Dlhotrvajúce kučery a vlny', 40],
                    ['melir', 'Melír', 'Cez čiapku alebo fóliový', 50],
                    ['ucesy', 'Účesy na príležitosť', 'Svadobné a slávnostné účesy', 60],
                ];
                foreach ($seed as $row) {
                    $b = new HomeBox();
                    $b->box_key = $row[0];
                    $b->title = $row[1];
                    $b->description = $row[2];
                    $b->sort_order = $row[3];
                    $b->save();
                }
            }
        } catch (\Throwable $e) {
            // ignore; view will still render empty list
        }

        if ($request->isPost()) {
            $titles = $request->value('title') ?? [];
            $descs = $request->value('description') ?? [];

            if (!is_array($titles)) { $titles = []; }
            if (!is_array($descs)) { $descs = []; }

            try {
                foreach ($titles as $id => $t) {
                    $id = (int)$id;
                    $title = trim((string)$t);
                    $desc = trim((string)($descs[$id] ?? ''));

                    if ($id <= 0) { continue; }
                    if ($title === '' || mb_strlen($title) < 2) { continue; }
                    if ($desc === '' || mb_strlen($desc) < 2) { continue; }

                    $box = HomeBox::getOne($id);
                    if (!$box) { continue; }
                    $box->title = $title;
                    $box->description = $desc;
                    $box->save();
                }

                return $this->redirect($this->url('admin.homeBoxes', ['flash' => 'ok']));
            } catch (\Throwable $e) {
                $errors['form'] = 'Uloženie zlyhalo. Skúste to prosím znova.';
            }
        }

        $flash = (string)($request->value('flash') ?? '');
        $boxes = [];
        try {
            $boxes = HomeBox::getAll(orderBy: '`sort_order` ASC, `id` ASC');
        } catch (\Throwable $e) {
            $boxes = [];
            $errors['form'] = 'Nepodarilo sa načítať okienka z DB.';
        }

        return $this->html(compact('boxes', 'errors', 'flash'), 'home-boxes');
    }
}
