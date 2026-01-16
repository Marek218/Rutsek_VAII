<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\ContactInfo|null $contactInfo */
/** @var array $errors */
/** @var array $old */
/** @var string $flash */
/** @var \Framework\Auth\AppUser $user */

// fallback values if DB row is missing
$ciSalon = $contactInfo?->salon_name ?? 'Kaderníctvo Luxer';
$ciPerson = $contactInfo?->person_name ?? 'Lucia Mojžitová';
$ciPhone = $contactInfo?->phone ?? '0903 842 887';
$ciEmail = $contactInfo?->email ?? 'info@luxer.sk';
$ciAddress = $contactInfo?->address_line ?? 'Beskydská 5006/1, 974 11 Banská Bystrica';
$ciLogo = 'images/logo.png';
$ciMap = $contactInfo?->map_embed_url ?? 'https://www.google.com/maps?q=Besky%CC%81dska+5006%2F1+97411+Banska+Bystrica&output=embed';
$ciOpening = $contactInfo?->opening_hours ?? "Pondelok – Piatok: 08:00 – 18:00\nSobota: 08:00 – 13:00\nNedeľa: Zatvorené";
?>
<!-- Wider centered wrapper: increases max width on large screens without touching footer or layout -->
<div style="max-width:1200px; width:100%; margin:0 auto; padding:0 12px;">

    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1>Kontakt</h1>
            <p>Kontaktujte nás pre objednávky alebo otázky — radi vám pomôžeme.</p>

            <?php if (isset($user) && $user->isLoggedIn()) { ?>
                <div class="d-flex gap-2 justify-content-center flex-wrap mt-2">
                    <a class="btn btn-sm btn-outline-secondary" href="<?= $link->url('admin.contact') ?>">Upraviť kontaktné údaje</a>
                    <a class="btn btn-sm btn-outline-primary" href="<?= $link->url('admin.messages') ?>">Správy (Napíšte nám)</a>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php if (($flash ?? '') === 'sent') { ?>
        <div class="alert alert-success">Správa bola odoslaná. Ďakujeme.</div>
    <?php } ?>

    <?php if (!empty($errors['form'])) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['form']) ?></div>
    <?php } ?>

    <!-- Top row: left = info card, right = map -->
    <div class="contact-grid">

        <div class="contact-left">
            <div class="card shadow-sm" style="border-radius:12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?= $link->asset($ciLogo) ?>" alt="Luxer logo" style="width:96px;height:auto;margin-right:14px;border-radius:8px;">
                        <div>
                            <h4 class="mb-0"><?= htmlspecialchars($ciSalon) ?></h4>
                            <div class="text-muted"><?= htmlspecialchars($ciPerson) ?></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <a href="tel:<?= htmlspecialchars(preg_replace('~[^0-9+]~', '', $ciPhone)) ?>" class="d-inline-flex align-items-center text-decoration-none contact-phone">
                            <span aria-hidden="true" style="font-size:1.05rem;margin-right:.5rem">📞</span>
                            <span class="fs-5 fw-semibold"><?= htmlspecialchars($ciPhone) ?></span>
                        </a>
                    </div>

                    <ul class="list-unstyled mb-3 contact-meta">
                        <li class="mb-1">
                            <span aria-hidden="true" style="margin-right:.5rem">📍</span>
                            <?= htmlspecialchars($ciAddress) ?>
                        </li>
                        <li class="mb-1">
                            <span aria-hidden="true" style="margin-right:.5rem">✉️</span>
                            <a href="mailto:<?= htmlspecialchars($ciEmail) ?>"><?= htmlspecialchars($ciEmail) ?></a>
                        </li>
                    </ul>

                    <h6 class="mt-3">Otváracie hodiny</h6>
                    <div class="mb-3">
                        <?= nl2br(htmlspecialchars($ciOpening)) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact-right">
            <div class="card shadow-sm" style="border-radius:12px;height:100%;overflow:hidden;">
                <iframe
                    src="<?= htmlspecialchars($ciMap) ?>"
                    width="100%" height="100%" style="min-height:480px;border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>

    </div>

    <!-- Full-width form card below -->
    <div class="contact-form-card">
        <div class="card shadow-sm" style="border-radius:12px;padding:1rem;">
            <div class="card-body">
                <h5 class="mb-3">Napíšte nám</h5>

                <?php if (!empty($errors)) { ?>
                    <div class="alert alert-warning">
                        Skontrolujte prosím formulár. Niektoré polia sú vyplnené nesprávne.
                    </div>
                <?php } ?>

                <form method="post" action="<?= $link->url('home.contact') ?>" class="contact-form" data-ajax-contact="1">
                    <!-- Honeypot anti-spam: musí zostať prázdne -->
                    <label>
                        <input type="text" name="website" value="" style="position:absolute;left:-9999px;top:-9999px" tabindex="-1" autocomplete="off" aria-hidden="true">
                    </label>

                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label for="contact-name" class="form-label">Meno</label>
                            <input id="contact-name" type="text" name="name" class="form-control<?= !empty($errors['name']) ? ' is-invalid' : '' ?>" value="<?= htmlspecialchars((string)($old['name'] ?? '')) ?>" required>
                            <?php if (!empty($errors['name'])) { ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php } ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="contact-email" class="form-label">Email</label>
                            <input id="contact-email" type="email" name="email" class="form-control<?= !empty($errors['email']) ? ' is-invalid' : '' ?>" value="<?= htmlspecialchars((string)($old['email'] ?? '')) ?>" required>
                            <?php if (!empty($errors['email'])) { ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php } ?>
                        </div>
                        <div class="col-12">
                            <label for="contact-message" class="form-label">Správa</label>
                            <textarea id="contact-message" name="message" class="form-control<?= !empty($errors['message']) ? ' is-invalid' : '' ?>" rows="4" required><?= htmlspecialchars((string)($old['message'] ?? '')) ?></textarea>
                            <?php if (!empty($errors['message'])) { ?><div class="invalid-feedback"><?= htmlspecialchars($errors['message']) ?></div><?php } ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
