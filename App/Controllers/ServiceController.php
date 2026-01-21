<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class ServiceController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    public function index(Request $request): Response
    {
        return $this->redirect($this->url('home.services'));
    }

    public function save(Request $request): Response
    {
        // delegate to home.services POST to avoid duplicate logic
        return $this->redirect($this->url('home.services'));
    }
}
