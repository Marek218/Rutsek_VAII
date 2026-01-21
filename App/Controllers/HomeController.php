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

        // Contact form POSTs are handled in MessageController::store

        return $this->html(compact('contactInfo', 'flash', 'errors', 'old'));
    }

    public function order(Request $request): Response
    {
        return $this->redirect($this->url('order.index'));
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
        // Redirect to GalleryController index which handles public/admin gallery rendering
        return $this->redirect($this->url('gallery.index'));
    }
}
