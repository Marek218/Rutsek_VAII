<?php

namespace App\Controllers;

use App\Models\Service;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;

class ServiceController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    public function index(Request $request): Response
    {
        return $this->services($request);
    }

    public function save(Request $request): Response
    {
        // delegate to home.services POST to avoid duplicate logic
        return $this->redirect($this->url('home.services'));
    }

    /**
     * Handle displaying and updating services (GET shows list, POST updates prices)
     */
    public function services(Request $request): Response
    {
        // If admin posts changes, process them here
        if ($request->isPost()) {
            if (!$this->user->isLoggedIn()) {
                return $this->redirect($this->url('home.services'));
            }
            $prices = $request->value('price') ?? [];
            if (!is_array($prices)) { $prices = []; }

            $errors = [];
            foreach ($prices as $id => $val) {
                $id = (int)$id;
                try {
                    Service::edit($id, ['price' => $val]);
                } catch (\Throwable $e) {
                    $errors[$id] = $e->getMessage();
                }
            }

            if (!empty($errors)) {
                // Re-render view with errors and old inputs
                $services = Service::getAll(orderBy: '`id` ASC');
                return $this->html(['errors' => $errors, 'old' => $request->post(), 'services' => $services], 'Home/services');
            }

            // redirect back to services without query params
            return $this->redirect($this->url('home.services'));
        }

        $services = Service::getAll(orderBy: '`id` ASC');
        return $this->html(compact('services'), 'Home/services');
    }
}
