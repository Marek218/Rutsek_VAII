<?php

namespace App\Controllers;

use App\Models\Gallery;
use App\Support\FileUpload;
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
                    $file = $request->file('image');
                    $result = FileUpload::storeGalleryFile($file, 'gallery');
                    if (!$result['ok']) {
                        $err = $result['error'] ?? 'uploaderror';
                        return $this->redirect($this->url('gallery.admin', ['flash' => 'badfile', 'err' => $err]));
                    }

                    $g = new Gallery();
                    $g->path_url = $result['path'];
                    $g->title = null;
                    $g->is_public = 1;
                    // determine next sort order
                    $items = Gallery::getAll(orderBy: '`sort_order` DESC', limit: 1);
                    $max = 0;
                    if (!empty($items)) { $max = (int)($items[0]->sort_order ?? 0); }
                    $g->sort_order = $max + 1;
                    $g->save();

                    return $this->redirect($this->url('gallery.admin', ['flash' => 'ok']));
                }

                if ($mode === 'delete') {
                    $id = (int)($request->value('id') ?? 0);
                    if ($id > 0) {
                        $item = Gallery::getOne($id);
                        if ($item) {
                            // try to unlink file (normalize path)
                            $path = Gallery::normalizePathUrl($item->path_url ?? null);
                            $publicDir = realpath(__DIR__ . '/../../public');
                            if ($path && $publicDir) {
                                $full = rtrim($publicDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
                                if (is_file($full)) { @unlink($full); }
                            }
                            $item->delete();
                        }
                    }
                    if ($request->isAjax()) { return new JsonResponse(['ok' => true, 'id' => $id]); }
                    return $this->redirect($this->url('gallery.admin', ['flash' => 'deleted']));
                }

                if ($mode === 'reorder') {
                    // order[] params
                    $post = $request->post() ?: [];
                    $order = $post['order'] ?? $post['order[]'] ?? [];
                    if (!is_array($order)) { $order = [$order]; }
                    $pos = 1;
                    foreach ($order as $id) {
                        $id = (int)$id;
                        $g = Gallery::getOne($id);
                        if ($g) { $g->sort_order = $pos++; $g->save(); }
                    }
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
