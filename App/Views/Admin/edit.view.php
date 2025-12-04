<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
/** @var \App\Models\Order $order */
?>

<div class="container">
    <div class="row mb-3">
        <div class="col">
            <h2>Upraviť rezerváciu #<?= (int)$order->id ?></h2>
            <a class="btn btn-link p-0" href="<?= $link->url('admin.index') ?>">← Späť na zoznam</a>
        </div>
    </div>

    <form method="post" action="<?= $link->url('admin.edit', ['id' => $order->id]) ?>" class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="first_name">Meno</label>
            <input id="first_name" type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($order->first_name ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="last_name">Priezvisko</label>
            <input id="last_name" type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($order->last_name ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" class="form-control" value="<?= htmlspecialchars($order->email ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="phone">Telefón</label>
            <input id="phone" type="text" name="phone" class="form-control" value="<?= htmlspecialchars($order->phone ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="service">Služba</label>
            <input id="service" type="text" name="service" class="form-control" value="<?= htmlspecialchars($order->service ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="date">Dátum</label>
            <input id="date" type="date" name="date" class="form-control" value="<?= htmlspecialchars($order->date ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="time">Čas</label>
            <input id="time" type="time" name="time" class="form-control" value="<?= htmlspecialchars(substr((string)$order->time, 0, 5)) ?>">
        </div>
        <div class="col-12">
            <label class="form-label" for="notes">Poznámka</label>
            <textarea id="notes" name="notes" class="form-control" rows="3"><?= htmlspecialchars($order->notes ?? '') ?></textarea>
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Uložiť zmeny</button>
            <a href="<?= $link->url('admin.index') ?>" class="btn btn-secondary">Zrušiť</a>
        </div>
    </form>
</div>
