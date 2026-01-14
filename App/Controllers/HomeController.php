<?php

namespace App\Controllers;

use App\Models\Gallery;
use App\Models\Order;
use App\Models\Service;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\JsonResponse;
use Framework\Http\Responses\RedirectResponse;
use Framework\Http\Responses\Response;

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
        return $this->html();
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
        // Render the original contact view again.
        return $this->html();
    }

    /**
     * Displays the about page.
     *
     * @return Response The response object containing the rendered HTML for the about page.
     */
    public function about(): Response
    {
        return $this->html();
    }

    // Changed to accept Request to match router calling convention and ensure user detection works
    public function order(Request $request): Response
    {
        // Use both controller-level user and app user to be robust
        $appUser = $this->app->getAppUser();
        if ($this->user->isLoggedIn() || $appUser->isLoggedIn()) {
            $orders = Order::getAll(orderBy: '`created_at` DESC');
            return $this->html(compact('orders'), 'Admin/index');
        }

        return $this->html();
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

    public function gallery(): Response
    {
        $galleryItems = [];
        $galleryError = null;

        try {
            $galleryItems = Gallery::getAll(whereClause: '`is_public` = 1', orderBy: '`sort_order` ASC, `id` ASC');
        } catch (\Throwable $e) {
            // Table likely not initialized yet; don't crash the whole site.
            $galleryError = $e->getMessage();
            $galleryItems = [];
        }

        return $this->html(compact('galleryItems', 'galleryError'));
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
