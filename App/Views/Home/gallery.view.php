<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Gallery[] $galleryItems */
/** @var string|null $galleryError */
/** @var bool $isAdmin */
/** @var string $flash */

use App\Models\Gallery;

$galleryItems = $galleryItems ?? [];
$galleryError = $galleryError ?? null;
$isAdmin = $isAdmin ?? false;
$flash = $flash ?? '';

$flashMessages = [
    'ok' => ['type' => 'success', 'text' => 'Obrázok bol pridaný do galérie.'],
    'deleted' => ['type' => 'success', 'text' => 'Obrázok bol odstránený.'],
    'uploaderror' => ['type' => 'danger', 'text' => 'Upload zlyhal. Skús to znova.'],
    'badtype' => ['type' => 'warning', 'text' => 'Povolené sú len PNG/JPG/JPEG.'],
    'storefail' => ['type' => 'danger', 'text' => 'Súbor sa nepodarilo uložiť na server.'],
    'nopublicdir' => ['type' => 'danger', 'text' => 'Nenašiel som public/ adresár (chybná konfigurácia projektu).'],
    'exception' => ['type' => 'danger', 'text' => 'Nastala chyba pri spracovaní galérie.'],
    'badfile' => ['type' => 'danger', 'text' => 'Súbor neprešiel validáciou.'],
];

$debug = isset($_GET['debug']) && $_GET['debug'] == '1';

?>

<div class="row mb-4">
    <div class="col-12 text-center">
        <h1>Galéria</h1>
        <p class="lead">Prezerajte si naše práce</p>
    </div>
</div>

<?php if ($isAdmin) { ?>
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0">Správa galérie</h5>
                <span class="text-muted small">Admin režim (pridanie / odstránenie fotiek)</span>
            </div>

            <?php if ($flash !== '' && isset($flashMessages[$flash])) {
                $m = $flashMessages[$flash]; ?>
                <div class="alert alert-<?= htmlspecialchars($m['type']) ?> mb-3"><?php
                    echo htmlspecialchars($m['text']);
                    // if there's a specific error message (err) from validation, show it (sanitized)
                    if (isset($_GET['err']) && trim((string)$_GET['err']) !== '') {
                        echo '<div class="mt-2"><small class="text-muted">' . htmlspecialchars((string)$_GET['err']) . '</small></div>';
                    }
                ?></div>
            <?php } ?>

            <form method="post" action="<?= $link->url('gallery.admin') ?>" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="mode" value="upload">
                <div class="col-12">
                    <label class="form-label">Obrázok (PNG/JPG)</label>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <input type="file" name="image" accept="image/png,image/jpeg" class="form-control form-control-file" required>
                        <button type="submit" class="btn btn-primary">Nahrať</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php } ?>

<?php if ($galleryError) { ?>
    <div class="alert alert-warning">
        Galéria zatiaľ nie je pripravená (chyba DB).<br>
        <?php if ($debug) { ?><small class="text-muted">Detail: <?= htmlspecialchars($galleryError) ?></small><?php } ?>
    </div>
<?php } ?>

<?php if (!$galleryError && empty($galleryItems)) { ?>
    <div class="alert alert-info">Galéria zatiaľ neobsahuje žiadne fotky.</div>
<?php } ?>

<?php if (!$galleryError && !empty($galleryItems)) { ?>
    <div
        class="gallery-grid"
        id="galleryGrid"
        <?= $isAdmin ? ' data-admin-reorder="1"' : '' ?>
        data-gallery-grid
        <?= $isAdmin ? (' data-reorder-endpoint="' . htmlspecialchars($link->url('gallery.admin')) . '" data-reorder-redirect="' . htmlspecialchars($link->url('gallery.admin', ['flash' => 'ok'])) . '"') : '' ?>
    >
        <?php foreach ($galleryItems as $item) {
            $path = Gallery::normalizePathUrl($item->path_url ?? null);
            if ($path === null) {
                continue;
            }

            $imgUrl = $link->asset($path);
            $title = trim((string)($item->title ?? ''));
            if ($title === '') {
                $title = 'Fotka #' . (int)($item->id ?? 0);
            }
            ?>

            <div class="gallery-thumb" data-gallery-item data-id="<?= (int)($item->id ?? 0) ?>"<?= $isAdmin ? ' draggable="true"' : '' ?>>
                <?php if ($debug) { ?>
                    <div class="visually-hidden" aria-hidden="true">
                        id=<?= (int)($item->id ?? 0) ?>; path_url=<?= htmlspecialchars((string)($item->path_url ?? '')) ?>; normalized=<?= htmlspecialchars((string)$path) ?>; src=<?= htmlspecialchars($imgUrl) ?>
                    </div>
                <?php } ?>

                <?php if ($isAdmin) { ?>
                    <form method="post"
                          action="<?= $link->url('gallery.admin') ?>"
                          class="gallery-admin-delete"
                          onsubmit="return confirm('Naozaj odstrániť tento obrázok?');">
                        <input type="hidden" name="mode" value="delete">
                        <input type="hidden" name="id" value="<?= (int)($item->id ?? 0) ?>">
                        <button type="submit" class="btn btn-sm btn-danger" title="Vymazať" aria-label="Vymazať">
                            ✕
                        </button>
                    </form>
                <?php } ?>

                <a href="#" data-bs-toggle="modal" data-bs-target="#galleryModal" data-img="<?= htmlspecialchars($imgUrl) ?>" data-title="<?= htmlspecialchars($title) ?>">
                    <span class="gallery-frame">
                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($title) ?>" loading="lazy" data-gallery-img>
                    </span>
                </a>

                <div class="gallery-error" data-gallery-error hidden>
                    <div><strong>Obrázok sa nepodarilo načítať</strong></div>
                    <?php if ($debug) { ?>
                        <div class="small mt-1" style="word-break:break-all;">src: <code><?= htmlspecialchars($imgUrl) ?></code></div>
                    <?php } ?>
                </div>

                <!-- caption removed on purpose (user requested no text under images) -->

                <?php if ($isAdmin) { ?>
                    <div class="p-2">
                        <div class="small text-muted">#<?= (int)($item->id ?? 0) ?> • <?= htmlspecialchars((string)($item->path_url ?? '')) ?></div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<!-- Modal (lightbox) -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="background:transparent;border:0;box-shadow:none;">
            <div class="modal-body p-0 position-relative" style="background:transparent;">
                <button type="button"
                        class="btn-close position-absolute end-0 m-3"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        style="z-index:2;opacity:0.9;filter:invert(1) drop-shadow(0 1px 2px rgba(0,0,0,.4));">
                </button>

                <div style="display:flex;align-items:center;justify-content:center;min-height:60vh;max-height:85vh;padding:0;background:transparent;">
                    <img
                        src=""
                        alt=""
                        id="galleryModalImage"
                        style="max-width:min(100%,1100px);max-height:85vh;width:auto;height:auto;object-fit:contain;display:block;border-radius:10px;box-shadow:0 16px 50px rgba(0,0,0,.35);background:transparent;"
                    >
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($isAdmin && $debug) {
    $last = Gallery::getUploadLogLines();
    if (!empty($last)) {
        echo '<div class="alert alert-secondary"><strong>Debug – posledné záznamy upload_errors.log:</strong><pre style="white-space:pre-wrap;max-height:200px;overflow:auto;padding:0.5rem;margin-top:.5rem;">' . htmlspecialchars(implode("\n", $last)) . '</pre></div>';
    } else {
        echo '<div class="alert alert-secondary"><strong>Debug:</strong> upload_errors.log nenájdený (uistite sa, že adresár var/log existuje a má zápis).</div>';
    }
    echo '<div class="small text-muted">Tip: Ak nahrávanie zlyhá, skontrolujte nastavenia PHP <code>upload_max_filesize</code> a <code>post_max_size</code> a práva adresára <code>public/uploads/gallery</code>. Max veľkosť uploadu nastavená v aplikácii: 5 MB.</div>';
}
?>
