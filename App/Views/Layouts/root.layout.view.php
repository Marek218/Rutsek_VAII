<?php

/** @var string $contentHTML */
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <title><?= App\Configuration::APP_NAME ?></title>
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $link->asset('favicons/logo.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $link->asset('favicons/logo.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $link->asset('favicons/logo.png') ?>">
    <link rel="manifest" href="<?= $link->asset('favicons/site.webmanifest') ?>">
    <link rel="shortcut icon" href="<?= $link->asset('favicons/favicon.ico') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="<?= $link->asset('css/styl.css') ?>?v=<?= time() ?>">
    <script src="<?= $link->asset('js/script.js') ?>"></script>


</head>
<body>
<nav class="navbar navbar-expand-sm bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= $link->url('home.index') ?>">
            <img src="<?= $link->asset('images/logo.png') ?>" title="<?= App\Configuration::APP_NAME ?>" alt="Framework Logo">
        </a>
        <ul class="navbar-nav me-auto">
            <li class="nav-item">
                <a class="nav-link" href="<?= $link->url('home.index') ?>">Domov</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $link->url('home.services') ?>">Služby a cenník</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $link->url('home.gallery') ?>">Galéria</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $link->url('home.about') ?>">O nás</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $link->url('home.order') ?>">Rezervácia</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $link->url('home.contact') ?>">Kontakt</a>
            </li>
            <?php if ($user->isLoggedIn()) { ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= $link->url('admin.index') ?>">Admin</a>
            </li>
            <?php } ?>

        </ul>
        <?php if ($user->isLoggedIn()) { ?>
            <span class="navbar-text">Logged in user: <b><?= $user->getName() ?></b></span>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $link->url('auth.logout') ?>">Log out</a>
                </li>
            </ul>
        <?php } else { ?>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= App\Configuration::LOGIN_URL ?>">Log in</a>
                </li>
            </ul>
        <?php } ?>

    </div>
</nav>
<div class="container-fluid mt-3">
    <div class="web-content">
        <?= $contentHTML ?>
    </div>
</div>

<footer class="site-footer">
    <div class="container">
        <div class="row">
            <!-- Stĺpec 1 -->
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold">Kaderníctvo Luxer</h5>
                <p class="mt-3">
                    Profesionálna starostlivosť o vaše vlasy v<br>
                    srdci Banskej Bystrice
                </p>
            </div>
            <!-- Stĺpec 2 -->
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold">Rýchle odkazy</h5>
                <ul class="list-unstyled mt-3">
                    <li><a href="<?= $link->url('home.index') ?>" class="footer-link">Domov</a></li>
                    <li><a href="<?= $link->url('home.services') ?>" class="footer-link">Služby a cenník</a></li>
                    <li><a href="<?= $link->url('home.gallery') ?>" class="footer-link">Galéria</a></li>
                    <li><a href="<?= $link->url('home.contact') ?>" class="footer-link">Kontakt</a></li>
                </ul>
            </div>

            <!-- Stĺpec 3 -->
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold">Kontakt</h5>
                <p class="mt-3 mb-1">Námestie Slobody 4<br>Banská Bystrica</p>
                <p class="mb-1">
                    <a class="footer-link" href="tel:+421XXXXXXXXX">+421 XXX XXX XXX</a>
                </p>
                <p>
                    <a class="footer-link" href="mailto:info@luxer.sk">info@luxer.sk</a>
                </p>
            </div>
        </div>
        <hr class="footer-separator">
        <div class="text-center mt-4 footer-copy">
            © <?= date('Y') ?> Kaderníctvo Luxer. Všetky práva vyhradené.
        </div>
    </div>
</footer>
</body>
</html>
