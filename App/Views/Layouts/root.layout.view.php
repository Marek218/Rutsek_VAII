<?php

/** @var string $contentHTML */
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <title><?= App\Configuration::APP_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <!-- Rozdelené CSS súbory: načítavajú sa v logickom poradí -->
    <link rel="stylesheet" href="<?= $link->asset('css/base.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/navbar.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/buttons.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/services.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/order.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/footer.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/utils.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/theme.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/gallery.css') ?>?v=<?= time() ?>">

    <script src="<?= $link->asset('js/theme.js') ?>" defer></script>
    <script src="<?= $link->asset('js/admin-orders.js') ?>" defer></script>
    <script src="<?= $link->asset('js/gallery.js') ?>" defer></script>
    <script src="<?= $link->asset('js/gallery-errors.js') ?>" defer></script>
    <script src="<?= $link->asset('js/order-ajax.js') ?>" defer></script>
    <script src="<?= $link->asset('js/admin-messages.js') ?>" defer></script>
    <script src="<?= $link->asset('js/contact-ajax.js') ?>" defer></script>

</head>
<body<?= $user->isLoggedIn() ? ' class="is-logged"' : '' ?>>
<nav class="navbar navbar-expand-sm bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= $link->url('home.index') ?>">
            <img src="<?= $link->asset('images/logo.png') ?>" title="<?= App\Configuration::APP_NAME ?>" alt="Framework Logo">
        </a>

        <!-- Hamburger toggler for small screens: opens an offcanvas side menu -->
        <button class="navbar-toggler d-block d-sm-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Otvoriť menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Desktop nav (hidden on xs) -->
        <ul class="navbar-nav me-auto d-none d-sm-flex">
            <li class="nav-item">
                <a class="nav-link" href="<?= $link->url('home.index') ?>">Domov</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $link->url('home.services') ?>">Služby a cenník</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $link->url('gallery.index') ?>">Galéria</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $user->isLoggedIn() ? $link->url('admin.orders') : $link->url('order.index') ?>">Rezervácia</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $link->url('home.contact') ?>">Kontakt</a>
            </li>
        </ul>

        <!-- User/login area (kept visible on sm+, hidden on xs - offcanvas shows it) -->
        <?php if ($user->isLoggedIn()) { ?>
            <span class="navbar-text d-none d-sm-inline">Logged in user: <b><?= $user->getName() ?></b></span>
            <ul class="navbar-nav ms-auto d-none d-sm-flex align-items-center">
                <li class="nav-item me-2">
                    <button type="button" class="theme-toggle" data-theme-toggle aria-label="Prepnúť motív">
                        <span class="dot" aria-hidden="true"></span>
                        <span class="label" data-theme-label>Motív</span>
                    </button>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $link->url('auth.logout') ?>">Log out</a>
                </li>
            </ul>
        <?php } else { ?>
            <ul class="navbar-nav ms-auto d-none d-sm-flex align-items-center">
                <li class="nav-item me-2">
                    <button type="button" class="theme-toggle" data-theme-toggle aria-label="Prepnúť motív">
                        <span class="dot" aria-hidden="true"></span>
                        <span class="label" data-theme-label>Motív</span>
                    </button>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= App\Configuration::LOGIN_URL ?>">Log in</a>
                </li>
            </ul>
        <?php } ?>

    </div>
</nav>

<!-- Offcanvas side menu for mobile (contains same links plus login/user) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasNavbarLabel"><?= App\Configuration::APP_NAME ?></h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Zavrieť"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="navbar-nav justify-content-start flex-grow-1 pe-3">
      <li class="nav-item"><a class="nav-link" href="<?= $link->url('home.index') ?>">Domov</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= $link->url('home.services') ?>">Služby a cenník</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= $link->url('gallery.index') ?>">Galéria</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= $user->isLoggedIn() ? $link->url('admin.orders') : $link->url('order.index') ?>">Rezervácia</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= $link->url('home.contact') ?>">Kontakt</a></li>
    </ul>
    <hr>

    <div class="mb-3">
        <button type="button" class="theme-toggle" data-theme-toggle aria-label="Prepnúť motív">
            <span class="dot" aria-hidden="true"></span>
            <span class="label" data-theme-label>Motív</span>
        </button>
    </div>

    <?php if ($user->isLoggedIn()) { ?>
      <div class="offcanvas-user">
        <div class="mb-2">Prihlásený ako: <strong><?= htmlspecialchars($user->getName()) ?></strong></div>
        <div class="d-flex gap-2 flex-wrap">
          <a class="btn btn-outline-secondary" href="<?= $link->url('auth.logout') ?>">Odhlásiť</a>
        </div>
      </div>
    <?php } else { ?>
      <a class="btn btn-primary" href="<?= App\Configuration::LOGIN_URL ?>">Prihlásiť sa</a>
    <?php } ?>
  </div>
</div>


<div class="container-fluid mt-3">
    <div class="web-content">
        <?= $contentHTML ?>
    </div>
</div>

<footer class="site-footer">
    <div class="container">
        <div class="row">
            <!-- Stĺpec 1 -->
            <div class="col-md-6 mb-4">
                <h5 class="fw-bold">Kaderníctvo Luxer</h5>
                <p class="mt-3">
                    Profesionálna starostlivosť o vaše vlasy v<br>
                    srdci Banskej Bystrice
                </p>
            </div>
            <!-- Stĺpec 2 (removed quick links) -->

            <!-- Stĺpec 3 (expanded) -->
            <div class="col-md-6 mb-4">
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