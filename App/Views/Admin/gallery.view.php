<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
/** @var \App\Models\Gallery[] $items */
/** @var string|null $error */
/** @var string $flash */

$items = $items ?? [];
$error = $error ?? null;
$flash = $flash ?? '';

$flashMessages = [
    'ok' => ['type' => 'success', 'text' => 'Obrázok bol pridaný do galérie.'],
    'deleted' => ['type' => 'success', 'text' => 'Obrázok bol odstránený.'],
    'nofile' => ['type' => 'warning', 'text' => 'Nevybral si žiadny súbor.'],
    'uploaderror' => ['type' => 'danger', 'text' => 'Upload zlyhal. Skús to znova.'],
    'badtype' => ['type' => 'warning', 'text' => 'Povolené sú len PNG/JPG/JPEG.'],
    'storefail' => ['type' => 'danger', 'text' => 'Súbor sa nepodarilo uložiť na server.'],
    'nopublicdir' => ['type' => 'danger', 'text' => 'Nenašiel som public/ adresár (chybná konfigurácia projektu).'],
];
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2>Galéria</h2>
            <p class="text-muted mb-0">Pridávaj a spravuj fotky v galérii.</p>
        </div>
        <div class="col-auto align-self-end">
            <a class="btn btn-outline-secondary" href="<?= $link->url('admin.index') ?>">← Späť</a>
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

            <form method="post" action="<?= $link->url('admin.galleryUpload') ?>" enctype="multipart/form-data" class="row g-3">
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
                                    <form method="post" action="<?= $link->url('admin.galleryDelete') ?>" onsubmit="return confirm('Naozaj odstrániť tento obrázok?');">
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
