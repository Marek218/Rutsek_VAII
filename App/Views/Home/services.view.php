<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
/** @var array $services */
?>

<div class="row">
    <div class="col">
        <h1>Naše služby a cenník</h1>
    </div>
</div>

<!-- center the content row -->
<div class="row mt-4 justify-content-center">
    <div class="col-lg-10 col-xl-9 mx-auto">
        <?php if ($user->isLoggedIn()) { ?>
            <form method="post" action="<?= $link->url('home.services') ?>">
        <?php } ?>

        <div class="table-card">
            <table class="table table-striped mx-auto services-table">
                <thead>
                <tr>
                    <th>Názov položky</th>
                    <th class="text-end">Cena v EURO</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($services ?? [] as $s) { ?>
                    <tr>
                        <td><?= htmlspecialchars($s->name) ?></td>
                        <td class="text-end">
                            <?php if ($user->isLoggedIn()) { ?>
                                <label>
                                    <input type="text" name="price[<?= (int)$s->id ?>]" value="<?= htmlspecialchars(number_format((float)$s->price, 2, '.', '')) ?>" />
                                </label> €
                            <?php } else { ?>
                                <?= htmlspecialchars(number_format((float)$s->price, 2, ',', '')) ?> €
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if ($user->isLoggedIn()) { ?>
            <div class="mt-2 text-center">
                <button class="btn btn-primary" type="submit">Uložiť ceny</button>
                <a class="btn btn-secondary" href="<?= $link->url('home.services') ?>">Zrušiť</a>
            </div>
            </form>
        <?php } ?>

        <!-- Informácie pod tabuľkou -->
        <div class="card p-3 mt-4">
            <h5 class="mb-2">Informácie</h5>
            <p class="mb-0">
                Cenník platný od 07.01.2025. Všetky ceny sú uvedené v eurách vrátane DPH.
            </p>
        </div>
    </div>
</div>