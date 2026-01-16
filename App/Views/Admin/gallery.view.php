<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
/** @var \App\Models\Gallery[] $items */
/** @var string|null $error */
/** @var string $flash */

$items = $items ?? [];
$error = $error ?? null;
$flash = $flash ?? '';

$flashMessages = [];
$flashMessages['ok'] = ['type' => 'success', 'text' => 'Obrazok bol pridany do galerie.'];
$flashMessages['deleted'] = ['type' => 'success', 'text' => 'Obrazok bol odstraneny.'];
$flashMessages['nofile'] = ['type' => 'warning', 'text' => 'Nevybral si ziaden subor.'];
$flashMessages['uploaderror'] = ['type' => 'danger', 'text' => 'Upload zlyhal. Skus to znova.'];
$flashMessages['badtype'] = ['type' => 'warning', 'text' => 'Povolene su len PNG/JPG/JPEG.'];
$flashMessages['storefail'] = ['type' => 'danger', 'text' => 'Subor sa nepodarilo ulozit na server.'];
$flashMessages['nopublicdir'] = ['type' => 'danger', 'text' => 'Nenasiel som public/ adresar (chybna konfiguracia projektu).'];
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2>Galéria</h2>
            <p class="text-muted mb-0">Pridávaj a spravuj fotky v galérii.</p>
        </div>
        <div class="col-auto align-self-end">
            <a class="btn btn-outline-secondary" href="<?= $link->url('Admin.index') ?>">← Späť</a>
        </div>
    </div>

    <?php if ($error) { ?>
        <div class="alert alert-warning">Chyba DB: <?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <?php if ($flash !== '' && isset($flashMessages[$flash])) {
        $m = $flashMessages[$flash]; ?>
        <div class="alert alert-<?= htmlspecialchars($m['type']) ?>"><?= htmlspecialchars($m['text']) ?></div>
    <?php } ?>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0">Pridať obrázok</h5>
                <span class="text-muted small">Klikni na “+” alebo vyber súbor</span>
            </div>

            <form method="post" action="<?= $link->url('Admin.galleryUpload') ?>" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="g_title">Názov (voliteľné)</label>
                    <input id="g_title" type="text" name="title" class="form-control" placeholder="napr. Pánsky strih">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="g_category">Kategória (voliteľné)</label>
                    <input id="g_category" type="text" name="category" class="form-control" placeholder="napr. Pánske">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="g_sort">Poradie</label>
                    <input id="g_sort" type="number" name="sort_order" class="form-control" value="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="g_public">Viditeľnosť</label>
                    <select id="g_public" name="is_public" class="form-select">
                        <option value="1" selected>Verejné</option>
                        <option value="0">Skryté</option>
                    </select>
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

    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">Aktuálne obrázky (<?= count($items) ?>)</h5>

            <?php if (empty($items)) { ?>
                <div class="text-muted">Zatiaľ tu nie sú žiadne obrázky.</div>
            <?php } else { ?>
                <div class="gallery-grid">
                    <?php foreach ($items as $it) {
                        $src = $link->asset((string)$it->path_url);
                        ?>
                        <div class="gallery-thumb">
                            <a href="<?= htmlspecialchars($src) ?>" target="_blank" title="Otvoriť obrázok">
                                <span class="gallery-frame">
                                    <img src="<?= htmlspecialchars($src) ?>" alt="">
                                </span>
                            </a>

                            <div class="p-2" style="background:#fff;">
                                <div class="small text-muted">#<?= (int)$it->id ?> • <?= htmlspecialchars((string)($it->path_url ?? '')) ?></div>
                                <div class="d-flex gap-2 mt-2">
                                    <form method="post" action="<?= $link->url('Admin.galleryDelete') ?>" onsubmit="return confirm('Naozaj odstrániť tento obrázok?');">
                                        <input type="hidden" name="id" value="<?= (int)$it->id ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Vymazať</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
