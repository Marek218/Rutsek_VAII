<?php

namespace App\Controllers;

use App\Models\HomeBox;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class HomeBoxController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        // require login for admin editing
        return $this->user->isLoggedIn();
    }

    public function index(Request $request): Response
    {
        $errors = [];
        $flash = (string)($request->value('flash') ?? '');
        $boxes = [];
        try {
            $boxes = HomeBox::getAll(orderBy: '`sort_order` ASC, `id` ASC');
        } catch (\Throwable $e) {
            $boxes = [];
            $errors['form'] = 'Nepodarilo sa načítať okienka z DB.';
        }

        return $this->html(compact('boxes', 'errors', 'flash'), 'Admin/home-boxes');
    }

    public function save(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('admin.homeBoxes'));
        }

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
            return $this->html(['errors' => $errors], 'Admin/home-boxes');
        }
    }
}
