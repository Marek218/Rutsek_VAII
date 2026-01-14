<?php

/** @var \Framework\Support\LinkGenerator $link */
?>
<!-- Wider centered wrapper: increases max width on large screens without touching footer or layout -->
<div style="max-width:1200px; width:100%; margin:0 auto; padding:0 12px;">

    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1 class="display-6">Kontakt</h1>
            <p class="lead">Kontaktujte nás pre objednávky alebo otázky — radi vám pomôžeme.</p>
        </div>
    </div>

    <!-- Top row: left = info card, right = map -->
    <div class="contact-grid">

        <div class="contact-left">
            <div class="card shadow-sm" style="border-radius:12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?= $link->asset('images/logo.png') ?>" alt="Luxer logo" style="width:96px;height:auto;margin-right:14px;border-radius:8px;">
                        <div>
                            <h4 class="mb-0">Kaderníctvo Luxer</h4>
                            <div class="text-muted">Lucia Mojžitová</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <a href="tel:+421903842887" class="d-inline-flex align-items-center text-decoration-none contact-phone">
                            <span aria-hidden="true" style="font-size:1.05rem;margin-right:.5rem">📞</span>
                            <span class="fs-5 fw-semibold">0903 842 887</span>
                        </a>
                    </div>

                    <ul class="list-unstyled mb-3 contact-meta">
                        <li class="mb-1">
                            <span aria-hidden="true" style="margin-right:.5rem">📍</span>
                            Beskydská 5006/1, 974 11 Banská Bystrica
                        </li>
                        <li class="mb-1">
                            <span aria-hidden="true" style="margin-right:.5rem">✉️</span>
                            <a href="mailto:info@luxer.sk">info@luxer.sk</a>
                        </li>
                    </ul>

                    <h6 class="mt-3">Otváracie hodiny</h6>
                    <div class="mb-3">
                        <div>Pondelok – Piatok: <strong>08:00 – 18:00</strong></div>
                        <div>Sobota: <strong>08:00 – 13:00</strong></div>
                        <div>Nedeľa: <strong>Zatvorené</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact-right">
            <div class="card shadow-sm" style="border-radius:12px;height:100%;overflow:hidden;">
                <iframe
                    src="https://www.google.com/maps?q=Beskýdska+5006%2F1+97411+Banska+Bystrica&output=embed"
                    width="100%" height="100%" style="min-height:480px;border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>

    </div>

    <!-- Full-width form card below -->
    <div class="contact-form-card">
        <div class="card shadow-sm" style="border-radius:12px;padding:1rem;">
            <div class="card-body">
                <h5 class="mb-3">Napíšte nám</h5>
                <form method="post" action="<?= $link->url('home.contact') ?>">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label for="contact-name" class="form-label">Meno</label>
                            <input id="contact-name" type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="contact-phone" class="form-label">Telefón</label>
                            <input id="contact-phone" type="tel" name="phone" class="form-control" placeholder="0903 842 887" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="contact-email" class="form-label">Email</label>
                            <input id="contact-email" type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label for="contact-message" class="form-label">Správa</label>
                            <textarea id="contact-message" name="message" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Odoslať správu</button>
                        <a href="tel:+421903842887" class="btn btn-outline-secondary btn-call">Zavolať</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
