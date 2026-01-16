<?php

namespace App\Controllers;

use App\Models\Gallery;
use App\Models\Order;
use App\Models\Service;
use App\Models\ContactMessage;
use App\Models\ContactInfo;
use App\Models\HomeBox;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\JsonResponse;
use Framework\Http\Responses\RedirectResponse;
use Framework\Http\Responses\Response;
use App\Configuration;

/**
 * Class HomeController
 * Handles actions related to the home page and other public actions.
 *
 * This controller includes actions that are accessible to all users, including a default landing page and a contact
 * page. It provides a mechanism for authorizing actions based on user permissions.
 *
 * @package App\Controllers
 */
class HomeController extends BaseController
{
    /**
     * Authorizes controller actions based on the specified action name.
     *
     * In this implementation, all actions are authorized unconditionally.
     *
     * @param string $action The action name to authorize.
     * @return bool Returns true, allowing all actions.
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Displays the default home page.
     *
     * This action serves the main HTML view of the home page.
     *
     * @return Response The response object containing the rendered HTML for the home page.
     */
    public function index(Request $request): Response
    {
        // Homepage boxes loaded from DB (falls back to static defaults in the view)
        $boxesByKey = [];
        try {
            $boxesByKey = HomeBox::getAllByKey();
        } catch (\Throwable $e) {
            $boxesByKey = [];
        }

        $isAdmin = $this->user->isLoggedIn();

        return $this->html(compact('boxesByKey', 'isAdmin'));
    }

    /**
     * Displays the contact page.
     *
     * This action serves the HTML view for the contact page, which is accessible to all users without any
     * authorization.
     *
     * @return Response The response object containing the rendered HTML for the contact page.
     */
    public function contact(Request $request): Response
    {
        $contactInfo = null;
        try {
            $contactInfo = ContactInfo::getSingleton();
        } catch (\Throwable $e) {
            $contactInfo = null;
        }

        $flash = (string)($request->value('flash') ?? '');
        $errors = [];
        $old = [];

        // Handle contact form submit
        if ($request->isPost()) {
            $old = $request->post() ?: [];

            $name = trim((string)$request->value('name'));
            $phone = trim((string)$request->value('phone'));
            $email = trim((string)$request->value('email'));
            $message = trim((string)$request->value('message'));

            // simple honeypot (bot should fill it)
            $website = trim((string)$request->value('website'));

            if ($website !== '') {
                // pretend success
                return $this->redirect($this->url('home.contact', ['flash' => 'sent']));
            }

            if ($name === '' || mb_strlen($name) < 2) {
                $errors['name'] = 'Meno musí mať aspoň 2 znaky.';
            }
            if ($phone === '' || mb_strlen($phone) < 6) {
                $errors['phone'] = 'Telefón je povinný (min. 6 znakov).';
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Zadajte platný email.';
            }
            if ($message === '' || mb_strlen($message) < 10) {
                $errors['message'] = 'Správa musí mať aspoň 10 znakov.';
            }

            if (empty($errors)) {
                try {
                    $msg = new ContactMessage();
                    $msg->name = $name;
                    $msg->phone = $phone;
                    $msg->email = $email;
                    $msg->subject = trim((string)$request->value('subject')) ?: null;
                    $msg->message = $message;
                    $msg->ip = (string)($request->server('REMOTE_ADDR') ?? '');
                    $msg->user_agent = (string)($request->server('HTTP_USER_AGENT') ?? '');
                    $msg->is_read = 0;
                    $msg->save();

                    return $this->redirect($this->url('home.contact', ['flash' => 'sent']));
                } catch (\Throwable $e) {
                    $errors['form'] = 'Správu sa nepodarilo uložiť. Skúste to prosím znova.';
                }
            }
        }

        return $this->html(compact('contactInfo', 'flash', 'errors', 'old'));
    }

    public function order(Request $request): Response
    {
        // If admin is logged in, show all reservations.
        $appUser = $this->app->getAppUser();
        $isAdmin = $this->user->isLoggedIn() || $appUser->isLoggedIn();

        if ($isAdmin) {
            $orders = Order::getAll(orderBy: '`created_at` DESC');
            return $this->html(compact('orders'), 'Admin/order');
        }

        // Otherwise show the public booking form.
        return $this->html([], 'order');
    }

    public function services(Request $request): Response
    {
        // If admin posts changes, process them here
        if ($request->isPost()) {
            if (!$this->user->isLoggedIn()) {
                return $this->redirect($this->url('home.services'));
            }
            $prices = $request->value('price') ?? [];
            if (!is_array($prices)) { $prices = []; }
            foreach ($prices as $id => $val) {
                $id = (int)$id;
                $price = trim((string)$val);
                // normalize decimal (allow comma)
                $price = str_replace(',', '.', $price);
                if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $price)) { continue; }
                $svc = Service::getOne($id);
                if ($svc) {
                    $svc->price = $price;
                    $svc->save();
                }
            }
            // redirect back to services without query params
            return $this->redirect($this->url('home.services'));
        }

        $services = Service::getAll(orderBy: '`id` ASC');
        return $this->html(compact('services'));
    }

    public function gallery(Request $request): Response
    {
        $galleryItems = [];
        $galleryError = null;

        $isAdmin = $this->user->isLoggedIn();
        $flash = (string)($request->value('flash') ?? '');

        // Handle admin actions on the same page
        if ($isAdmin && $request->isPost()) {
            $mode = (string)($request->value('mode') ?? '');

            try {
                if ($mode === 'upload') {
                    $file = $request->file('image');
                    if (!$file || !$file->isOk()) {
                        return $this->redirect($this->url('home.gallery', ['flash' => 'uploaderror']));
                    }

                    $origName = $file->getName();
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
                        return $this->redirect($this->url('home.gallery', ['flash' => 'badtype']));
                    }

                    $publicDir = realpath(__DIR__ . '/../../public');
                    if ($publicDir === false) {
                        return $this->redirect($this->url('home.gallery', ['flash' => 'nopublicdir']));
                    }
                    $uploadDir = rtrim($publicDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . Configuration::UPLOAD_DIR . 'gallery';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }

                    $random = bin2hex(random_bytes(8));
                    $baseName = pathinfo($origName, PATHINFO_FILENAME);
                    $baseName = preg_replace('~[^a-z0-9_-]+~i', '-', $baseName);
                    $baseName = trim($baseName, '-');
                    if ($baseName === '') { $baseName = 'image'; }

                    $fileName = $baseName . '-' . date('Ymd-His') . '-' . $random . '.' . $ext;
                    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

                    if (!$file->store($targetPath)) {
                        return $this->redirect($this->url('home.gallery', ['flash' => 'storefail']));
                    }

                    $item = new Gallery();
                    $item->title = trim((string)$request->value('title')) ?: null;
                    $item->category = trim((string)$request->value('category')) ?: null;
                    $item->is_public = (int)($request->value('is_public') ?? 1);
                    $item->sort_order = (int)($request->value('sort_order') ?? 0);
                    $item->path_url = 'uploads/gallery/' . $fileName;
                    $item->save();

                    return $this->redirect($this->url('home.gallery', ['flash' => 'ok']));
                }

                if ($mode === 'delete') {
                    $id = (int)($request->value('id') ?? 0);
                    if ($id > 0) {
                        $item = Gallery::getOne($id);
                        if ($item) {
                            $rel = (string)($item->path_url ?? '');
                            $publicDir = realpath(__DIR__ . '/../../public');
                            if ($publicDir !== false) {
                                $abs = rtrim($publicDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($rel, '/'));
                                if (is_file($abs)) {
                                    @unlink($abs);
                                }
                            }
                            $item->delete();
                        }
                    }
                    return $this->redirect($this->url('home.gallery', ['flash' => 'deleted']));
                }
            } catch (\Throwable $e) {
                return $this->redirect($this->url('home.gallery', ['flash' => 'exception']));
            }
        }

        try {
            if ($isAdmin) {
                // Admin sees all
                $galleryItems = Gallery::getAll(orderBy: '`sort_order` ASC, `id` ASC');
            } else {
                $galleryItems = Gallery::getAll(whereClause: '`is_public` = 1', orderBy: '`sort_order` ASC, `id` ASC');
            }
        } catch (\Throwable $e) {
            $galleryError = $e->getMessage();
            $galleryItems = [];
        }

        return $this->html(compact('galleryItems', 'galleryError', 'isAdmin', 'flash'));
    }

    /**
     * Handles the order submission from the booking form.
     * Persists into MariaDB `orders` table and redirects back to home with a flash-like query param.
     */
    public function orderSubmit(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('home.order'));
        }

        // Basic server-side validation and normalization
        $first = trim((string)$request->value('first_name'));
        $last = trim((string)$request->value('last_name'));
        $email = trim((string)$request->value('email'));
        $phone = trim((string)$request->value('phone'));
        $service = trim((string)$request->value('service'));
        $date = trim((string)$request->value('date'));
        $time = trim((string)$request->value('time'));
        $notes = trim((string)$request->value('notes'));

        // Minimal validation
        $errors = [];
        if ($first === '') { $errors['first_name'] = 'Meno je povinné.'; }
        if ($last === '') { $errors['last_name'] = 'Priezvisko je povinné.'; }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Neplatný email.'; }
        if ($phone === '') { $errors['phone'] = 'Telefón je povinný.'; }
        if ($service === '') { $errors['service'] = 'Vyberte službu.'; }
        if ($date === '') { $errors['date'] = 'Dátum je povinný.'; }
        if ($time === '') { $errors['time'] = 'Čas je povinný.'; }

        if (!empty($errors)) {
            // Return JSON errors if requested via XHR, else render order view with errors so user sees validation messages
            if ($request->isAjax()) {
                return new JsonResponse(['ok' => false, 'errors' => $errors], 422);
            }
            // Non-AJAX: re-render the order page with validation errors and old input
            return $this->html(['errors' => $errors, 'old' => $request->post()]);
        }

        // Normalize time to HH:MM:SS
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            $time .= ':00';
        }

        $order = new Order();
        $order->first_name = $first;
        $order->last_name = $last;
        $order->email = $email;
        $order->phone = $phone;
        $order->service = $service;
        $order->date = $date;   // expects Y-m-d
        $order->time = $time;   // expects HH:MM:SS
        $order->notes = $notes ?: null;

        // Persist using base Model::save()
        try {
            $order->save();
        } catch (\Exception $e) {
            // Return clear JSON error so developer can see the problem in Network tab
            if ($request->isAjax() || $request->wantsJson()) {
                return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
            }
            // Non-AJAX: re-render order page with error message and old input so user sees problem
            return $this->html(['error' => $e->getMessage(), 'old' => $request->post()]);
        }

        if ($request->isAjax()) {
            return new JsonResponse(['ok' => true, 'id' => $order->id]);
        }
        // redirect after success
        return $this->redirect($this->url('home.index'));
    }
}
