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
    <div class="col-md-8 mx-auto">
        <?php if ($user->isLoggedIn()) { ?>
            <form method="post" action="<?= $link->url('home.services') ?>">
        <?php } ?>

        <table class="table table-striped mx-auto">
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
                                <input type="text" name="price[<?= (int)$s->id ?>]" value="<?= htmlspecialchars(number_format((float)$s->price, 2, '.', '')) ?>" style="width:90px; text-align:right;" />
                            </label> €
                        <?php } else { ?>
                            <?= htmlspecialchars(number_format((float)$s->price, 2, ',', '')) ?> €
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <?php if ($user->isLoggedIn()) { ?>
            <div class="mt-2 text-center">
                <button class="btn btn-primary" type="submit">Uložiť ceny</button>
                <a class="btn btn-secondary" href="<?= $link->url('home.services') ?>">Zrušiť</a>
            </div>
            </form>
        <?php } ?>

    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Informácie</h5>
            <p>
                Cenník platný od 07.01.2025. Všetky ceny sú uvedené v eurách vrátane DPH.
            </p>
        </div>
    </div>
</div>