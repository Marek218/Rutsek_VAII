<?php

namespace App\Controllers;

use App\Models\Gallery;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\JsonResponse;
use Framework\Http\Responses\Response;

class GalleryController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    // Public gallery view
    public function index(Request $request): Response
    {
        $galleryItems = [];
        $galleryError = null;
        $isAdmin = $this->user->isLoggedIn();

        try {
            if ($isAdmin) {
                $galleryItems = Gallery::getAll(orderBy: '`sort_order` ASC, `id` ASC');
            } else {
                $galleryItems = Gallery::getAll(whereClause: '`is_public` = 1', orderBy: '`sort_order` ASC, `id` ASC');
            }
        } catch (\Throwable $e) {
            $galleryError = $e->getMessage();
            $galleryItems = [];
        }

        return $this->html(compact('galleryItems', 'galleryError', 'isAdmin'), 'Home/gallery');
    }

    // Admin index for gallery management (upload/delete/reorder)
    public function admin(Request $request): Response
    {
        if (!$this->user->isLoggedIn()) {
            return $this->redirect($this->url('admin.index'));
        }

        if ($request->isPost()) {
            $mode = (string)($request->value('mode') ?? '');

            try {
                if ($mode === 'upload') {
                    // delegate upload handling (validation, storing, DB insert) to model
                    $file = $request->file('image');
                    $result = Gallery::handleUpload($file);
                    if (!$result['ok']) {
                        return $this->redirect($this->url('gallery.admin', ['flash' => 'badfile', 'err' => $result['error']]));
                    }

                    return $this->redirect($this->url('gallery.admin', ['flash' => 'ok']));
                }

                if ($mode === 'delete') {
                    $id = (int)($request->value('id') ?? 0);
                    if ($id > 0) {
                        // model decides if delete is allowed and removes file + DB row
                        Gallery::deleteById($id);
                    }
                    if ($request->isAjax()) { return new JsonResponse(['ok' => true, 'id' => $id]); }
                    return $this->redirect($this->url('gallery.admin', ['flash' => 'deleted']));
                }

                if ($mode === 'reorder') {
                    // order[] params
                    $post = $request->post() ?: [];
                    $order = $post['order'] ?? $post['order[]'] ?? [];
                    if (!is_array($order)) { $order = [$order]; }
                    // model handles transactional reorder
                    Gallery::reorder($order);
                    if ($request->isAjax()) { return new JsonResponse(['ok' => true]); }
                    return $this->redirect($this->url('gallery.admin', ['flash' => 'ok']));
                }

            } catch (\Throwable $e) {
                if ($request->isAjax()) { return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500); }
                return $this->redirect($this->url('gallery.admin', ['flash' => 'exception']));
            }
        }

        return $this->index($request);
    }
}
