<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
/** @var \App\Models\Order[] $orders */
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2>Moje rezervácie</h2>
            <p class="text-muted mb-0">Prehľad všetkých rezervácií s možnosťou úprav a vymazania.</p>
        </div>
        <div class="col-auto align-self-end">
            <span class="small">Prihlásený: <strong><?= htmlspecialchars($user->getName()) ?></strong></span>
        </div>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'notfound') { ?>
        <div class="alert alert-warning">Rezervácia nebola nájdená.</div>
    <?php } ?>

    <div class="table-responsive admin-table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead>
            <tr>
                <th>Meno</th>
                <th>Email</th>
                <th>Telefón</th>
                <th>Služba</th>
                <th>Dátum</th>
                <th>Čas</th>
                <th class="text-end">Akcie</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($orders)) { foreach ($orders as $o) { ?>
                <tr>
                    <td><?= htmlspecialchars(($o->first_name ?? '') . ' ' . ($o->last_name ?? '')) ?></td>
                    <td><a href="mailto:<?= htmlspecialchars($o->email ?? '') ?>"><?= htmlspecialchars($o->email ?? '') ?></a></td>
                    <td><a href="tel:<?= htmlspecialchars($o->phone ?? '') ?>"><?= htmlspecialchars($o->phone ?? '') ?></a></td>
                    <td><?= htmlspecialchars($o->service ?? '') ?></td>
                    <td><?= htmlspecialchars($o->date ?? '') ?></td>
                    <td><?= htmlspecialchars(substr((string)$o->time, 0, 5)) ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-primary" href="<?= $link->url('admin.edit', ['id' => $o->id]) ?>">Upraviť</a>
                        <form action="<?= $link->url('admin.delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Naozaj chcete vymazať túto rezerváciu?');">
                            <input type="hidden" name="id" value="<?= (int)$o->id ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Vymazať</button>
                        </form>
                    </td>
                </tr>
            <?php } } else { ?>
                <tr><td colspan="7" class="text-center text-muted">Zatiaľ neexistujú žiadne rezervácie.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>