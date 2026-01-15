<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Gallery[] $galleryItems */
/** @var string|null $galleryError */
/** @var bool $isAdmin */
/** @var string $flash */

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
];

$debug = isset($_GET['debug']) && $_GET['debug'] == '1';

/**
 * Normalize DB value (path_url) into a path relative to public/.
 * Accepts values like:
 *  - images/Gallery/panske1.png
 *  - /images/Gallery/panske1.png
 *  - public/images/Gallery/panske1.png
 *  - images/Gallery/panske1   (no extension)
 */
$normalizePathUrl = function (?string $raw) {
    $path = trim((string)$raw);
    if ($path === '') {
        return null;
    }

    // Decode HTML entities just in case
    $path = html_entity_decode($path, ENT_QUOTES | ENT_HTML5);

    // Make it relative
    $path = ltrim($path, "/\\");

    // If someone stored filesystem-ish prefix
    if (stripos($path, 'public/') === 0) {
        $path = substr($path, strlen('public/'));
        $path = ltrim($path, "/\\");
    }

    // Normalize slashes for URL
    $path = str_replace('\\', '/', $path);

    // If no extension, try common image extensions
    if (!preg_match('~\.(png|jpe?g|webp|gif)$~i', $path)) {
        foreach (['.jpg', '.png', '.jpeg', '.webp'] as $ext) {
            // We can't truly check file existence here reliably in all envs,
            // but we can at least generate a valid candidate.
            // The browser will request it and show overlay if missing.
            return $path . $ext;
        }
    }

    return $path;
};
?>

<div class="row mb-4">
    <div class="col-12 text-center">
        <h1 class="display-6">Galéria</h1>
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
                <div class="alert alert-<?= htmlspecialchars($m['type']) ?> mb-3"><?= htmlspecialchars($m['text']) ?></div>
            <?php } ?>

            <form method="post" action="<?= $link->url('home.gallery') ?>" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="mode" value="upload">

                <div class="col-md-4">
                    <label class="form-label">Názov (voliteľné)</label>
                    <label>
                        <input type="text" name="title" class="form-control" placeholder="napr. Pánsky strih">
                    </label>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategória (voliteľné)</label>
                    <label>
                        <input type="text" name="category" class="form-control" placeholder="napr. Pánske">
                    </label>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Poradie</label>
                    <label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </label>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Viditeľnosť</label>
                    <label>
                        <select name="is_public" class="form-select">
                            <option value="1" selected>Verejné</option>
                            <option value="0">Skryté</option>
                        </select>
                    </label>
                </div>

                <div class="col-12">
                    <label class="form-label">Obrázok (PNG/JPG)</label>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <label class="btn btn-primary mb-0" style="cursor:pointer;">
                            +
                            <input type="file" name="image" accept="image/png,image/jpeg" hidden required>
                        </label>
                        <button type="submit" class="btn btn-outline-secondary">Nahrať</button>
                        <span class="text-muted small">Súbor sa uloží do <code>public/uploads/gallery</code> a do DB do <code>path_url</code>.</span>
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
    <div class="gallery-grid">
        <?php foreach ($galleryItems as $item) {
            $path = $normalizePathUrl($item->path_url ?? null);
            if ($path === null) {
                continue;
            }

            // DB stores a local path relative to public/ (e.g. uploads/x.jpg)
            $imgUrl = $link->asset($path);

            $title = trim((string)($item->title ?? ''));
            if ($title === '') {
                $title = 'Fotka #' . (int)($item->id ?? 0);
            }

            $meta = trim((string)($item->category ?? ''));
            ?>

            <div class="gallery-thumb" data-gallery-item>
                <?php if ($debug) { ?>
                    <div class="visually-hidden" aria-hidden="true">
                        id=<?= (int)($item->id ?? 0) ?>; path_url=<?= htmlspecialchars((string)($item->path_url ?? '')) ?>; normalized=<?= htmlspecialchars((string)$path) ?>; src=<?= htmlspecialchars($imgUrl) ?>
                    </div>
                <?php } ?>

                <?php if ($isAdmin) { ?>
                    <form method="post"
                          action="<?= $link->url('home.gallery') ?>"
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
                    <div class="p-2" style="background:#fff;">
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

<style>
    /* subtle blurred backdrop for nicer focus */
    #galleryModal.modal { --bs-modal-backdrop-bg: rgba(15, 18, 25); --bs-modal-backdrop-opacity: .25; }

    .gallery-admin-delete {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 3;
        margin: 0;
    }
    .gallery-admin-delete .btn {
        border-radius: 999px;
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 18px rgba(0,0,0,0.25);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function(){

         // Show error overlay when an image fails
         document.querySelectorAll('[data-gallery-item] img[data-gallery-img]').forEach(function(img){
             img.addEventListener('error', function(){
                 try {
                     var tile = img.closest('[data-gallery-item]');
                     if (tile) {
                         tile.classList.add('is-broken');
                         var err = tile.querySelector('[data-gallery-error]');
                         if (err) err.hidden = false;
                     }
                 } catch (e) {}
             }, { once: true });
         });

         // Lightbox
         var galleryModal = document.getElementById('galleryModal');
         if (!galleryModal) return;

         galleryModal.addEventListener('show.bs.modal', function (event) {
             var trigger = event.relatedTarget;
             if (!trigger) return;
             var img = trigger.getAttribute('data-img');

             var modalImg = document.getElementById('galleryModalImage');
             if (modalImg) {
                 modalImg.src = img || '';
                 // keep alt meaningful, title is still used for accessibility, but not displayed
                 modalImg.alt = trigger.getAttribute('data-title') || '';
             }
         });

         galleryModal.addEventListener('hidden.bs.modal', function () {
             var modalImg = document.getElementById('galleryModalImage');
             if (modalImg) {
                 modalImg.src = '';
                 modalImg.alt = '';
             }
         });
     });
</script>
