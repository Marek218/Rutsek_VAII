<?php

namespace App\Controllers;

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

    public function order(): Response
    {
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
            return $this->redirect($this->url('home.services', ['saved' => 1]));
        }

        $services = Service::getAll(orderBy: '`id` ASC');
        return $this->html(compact('services'));
    }

    public function gallery(): Response
    {
        return $this->html();
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
            // Return JSON errors if requested via XHR, else redirect with query params
            if ($request->isAjax()) {
                return new JsonResponse(['ok' => false, 'errors' => $errors], 422);
            }
            return $this->redirect($this->url('home.order', ['error' => 'validation']));
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
        $order->save();

        if ($request->isAjax()) {
            return new JsonResponse(['ok' => true, 'id' => $order->id]);
        }
        return $this->redirect($this->url('home.index', ['order' => 'ok']));
    }
}
