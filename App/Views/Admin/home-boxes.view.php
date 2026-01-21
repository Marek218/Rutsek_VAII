<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
/** @var array $boxes */
/** @var array $errors */
/** @var string $flash */

$boxes = $boxes ?? [];
$errors = $errors ?? [];
$flash = $flash ?? '';
?>

<div class="container">
    <div class="row mb-3">
        <div class="col">
            <h2>Úvodná stránka – okienka</h2>
            <p class="text-muted mb-0">Tu vieš upraviť texty v okienkach na úvodnej stránke.</p>
        </div>
    </div>

    <?php if ($flash === 'ok') { ?>
        <div class="alert alert-success">Uložené.</div>
    <?php } ?>

    <?php if (!empty($errors['form'])) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['form']) ?></div>
    <?php } ?>

    <form method="post" action="<?= $link->url('admin.homeBoxes') ?>" class="row g-3">
        <?php foreach ($boxes as $b) { ?>
            <input type="hidden" name="id[]" value="<?= (int)$b->id ?>">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius:12px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0"><?= htmlspecialchars((string)$b->box_key) ?></h5>
                        </div>

                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Nadpis</label>
                                <label>
                                    <input class="form-control" name="title[<?= (int)$b->id ?>]" value="<?= htmlspecialchars((string)$b->title) ?>" required>
                                </label>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Popis</label>
                                <label>
                                    <input class="form-control" name="description[<?= (int)$b->id ?>]" value="<?= htmlspecialchars((string)$b->description) ?>" required>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Uložiť</button>
            <a href="<?= $link->url('home.index') ?>" class="btn btn-secondary">Domov</a>
        </div>
    </form>
</div>
