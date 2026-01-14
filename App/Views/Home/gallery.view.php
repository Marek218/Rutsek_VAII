<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Gallery[] $galleryItems */
/** @var string|null $galleryError */

$galleryItems = $galleryItems ?? [];
$galleryError = $galleryError ?? null;

$debug = isset($_GET['debug']) && $_GET['debug'] == '1';
?>

<div class="row mb-4">
    <div class="col-12 text-center">
        <h1 class="display-6">Galéria</h1>
        <p class="lead">Prezerajte si naše práce</p>
    </div>
</div>

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
            // Primary field requested: path_url
            $candidate = trim((string)($item->path_url ?? ''));

            // Fallbacks (for compatibility)
            if ($candidate === '') {
                $candidate = trim((string)($item->image_url ?? ''));
            }
            if ($candidate === '') {
                $candidate = trim((string)($item->image_path ?? ''));
            }

            $candidate = html_entity_decode($candidate, ENT_QUOTES | ENT_HTML5);

            if ($candidate === '') {
                continue;
            }

            // Common: "www..." without scheme
            if (preg_match('~^www\.~i', $candidate) === 1) {
                $candidate = 'https://' . $candidate;
            }

            // Decide absolute vs local asset
            $isAbsolute = preg_match('~^https?://~i', $candidate) === 1;
            $imgUrl = $isAbsolute ? $candidate : $link->asset(ltrim($candidate, '/'));

            $title = trim((string)($item->title ?? ''));
            if ($title === '') {
                $title = 'Fotka #' . (int)($item->id ?? 0);
            }

            $meta = trim((string)($item->category ?? ''));
            ?>

            <div class="gallery-thumb" data-gallery-item>
                <?php if ($debug) { ?>
                    <div class="visually-hidden" aria-hidden="true">
                        id=<?= (int)($item->id ?? 0) ?>; path_url=<?= htmlspecialchars((string)($item->path_url ?? '')) ?>; src=<?= htmlspecialchars($imgUrl) ?>
                    </div>
                <?php } ?>

                <a href="#" data-bs-toggle="modal" data-bs-target="#galleryModal" data-img="<?= htmlspecialchars($imgUrl) ?>" data-title="<?= htmlspecialchars($title) ?>">
                    <img
                        src="<?= htmlspecialchars($imgUrl) ?>"
                        alt="<?= htmlspecialchars($title) ?>"
                        loading="lazy"
                        data-gallery-img
                        <?php if ($isAbsolute) { ?>referrerpolicy="no-referrer" crossorigin="anonymous"<?php } ?>
                    >
                </a>

                <div class="gallery-error" data-gallery-error hidden>
                    <div><strong>Obrázok sa nepodarilo načítať</strong></div>
                    <?php if ($debug) { ?>
                        <div class="small mt-1" style="word-break:break-all;">src: <code><?= htmlspecialchars($imgUrl) ?></code></div>
                    <?php } ?>
                </div>

                <div class="gallery-caption">
                    <div class="title">
                        <?= htmlspecialchars($title) ?>
                        <?php if ($isAbsolute) { ?><span class="gallery-badge">URL</span><?php } ?>
                    </div>
                    <?php if ($meta !== '') { ?><div class="meta"><?= htmlspecialchars($meta) ?></div><?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<!-- Modal (lightbox) -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body p-0" style="background:#000;">
                <button type="button" class="btn-close btn-close-white position-absolute end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <img src="" alt="" id="galleryModalImage" style="width:100%;height:auto;display:block;">
            </div>
            <div class="modal-footer">
                <h5 id="galleryModalTitle" class="m-0"></h5>
            </div>
        </div>
    </div>
</div>

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
             var title = trigger.getAttribute('data-title');
             var modalImg = document.getElementById('galleryModalImage');
             var modalTitle = document.getElementById('galleryModalTitle');
             if (modalImg) modalImg.src = img || '';
             if (modalTitle) modalTitle.textContent = title || '';
         });

         galleryModal.addEventListener('hidden.bs.modal', function () {
             var modalImg = document.getElementById('galleryModalImage');
             var modalTitle = document.getElementById('galleryModalTitle');
             if (modalImg) modalImg.src = '';
             if (modalTitle) modalTitle.textContent = '';
         });
     });
</script>
