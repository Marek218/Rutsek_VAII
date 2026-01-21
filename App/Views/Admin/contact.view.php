<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
/** @var \App\Models\ContactInfo $contact */
/** @var array $errors */

$errors = $errors ?? [];
?>

<div class="container">
    <div class="row mb-3">
        <div class="col">
            <h2>Kontakt – údaje</h2>
            <p class="text-muted mb-0">Tu upravíš údaje, ktoré sa zobrazujú na stránke Kontakt (telefón, adresa, mapa...).</p>
            <a class="btn btn-link p-0" href="<?= $link->url('home.contact') ?>">← Späť na Kontakt</a>
        </div>
    </div>

    <?php if (!empty($errors['form'])) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['form']) ?></div>
    <?php } ?>

    <form method="post" action="<?= $link->url('contactInfo.edit') ?>" class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="salon_name">Názov salónu</label>
            <input id="salon_name" name="salon_name" class="form-control<?= !empty($errors['salon_name']) ? ' is-invalid' : '' ?>" value="<?= htmlspecialchars((string)($contact->salon_name ?? '')) ?>" required>
            <?php if (!empty($errors['salon_name'])) { ?><div class="invalid-feedback"><?= htmlspecialchars($errors['salon_name']) ?></div><?php } ?>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="person_name">Meno (osoba)</label>
            <input id="person_name" name="person_name" class="form-control" value="<?= htmlspecialchars((string)($contact->person_name ?? '')) ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label" for="phone">Telefón</label>
            <input id="phone" name="phone" class="form-control<?= !empty($errors['phone']) ? ' is-invalid' : '' ?>" value="<?= htmlspecialchars((string)($contact->phone ?? '')) ?>" required>
            <?php if (!empty($errors['phone'])) { ?><div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div><?php } ?>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input id="email" name="email" type="email" class="form-control<?= !empty($errors['email']) ? ' is-invalid' : '' ?>" value="<?= htmlspecialchars((string)($contact->email ?? '')) ?>" required>
            <?php if (!empty($errors['email'])) { ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php } ?>
        </div>

        <div class="col-12">
            <label class="form-label" for="address_line">Adresa</label>
            <input id="address_line" name="address_line" class="form-control<?= !empty($errors['address_line']) ? ' is-invalid' : '' ?>" value="<?= htmlspecialchars((string)($contact->address_line ?? '')) ?>" required>
            <?php if (!empty($errors['address_line'])) { ?><div class="invalid-feedback"><?= htmlspecialchars($errors['address_line']) ?></div><?php } ?>
        </div>

        <div class="col-12">
            <label class="form-label" for="opening_hours">Otváracie hodiny (1 riadok = 1 deň)</label>
            <textarea id="opening_hours" name="opening_hours" class="form-control" rows="4"><?= htmlspecialchars((string)($contact->opening_hours ?? '')) ?></textarea>
        </div>

        <div class="col-12">
            <label class="form-label" for="map_embed_url">Mapa (embed URL)</label>
            <input id="map_embed_url" name="map_embed_url" class="form-control" value="<?= htmlspecialchars((string)($contact->map_embed_url ?? '')) ?>">
            <div class="form-text">Použi URL vo formáte <code>https://www.google.com/maps?q=...&output=embed</code></div>
        </div>

        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Uložiť</button>
            <a href="<?= $link->url('home.contact') ?>" class="btn btn-secondary">Zrušiť</a>
        </div>
    </form>
</div>
