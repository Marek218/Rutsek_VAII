<?php

/** @var string $contentHTML */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var \Framework\Support\LinkGenerator $link */
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <title><?= App\Configuration::APP_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $link->asset('favicons/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $link->asset('favicons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $link->asset('favicons/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= $link->asset('favicons/site.webmanifest') ?>">
    <link rel="shortcut icon" href="<?= $link->asset('favicons/favicon.ico') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"></script>

    <!-- Use the same styling pipeline as the main layout (no empty styl.css) -->
    <link rel="stylesheet" href="<?= $link->asset('css/base.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/buttons.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/utils.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/theme.css') ?>?v=<?= time() ?>">

    <script src="<?= $link->asset('js/theme.js') ?>" defer></script>
    <script src="<?= $link->asset('js/admin-orders.js') ?>" defer></script>
    <script src="<?= $link->asset('js/gallery.js') ?>" defer></script>
    <script src="<?= $link->asset('js/order-ajax.js') ?>" defer></script>
    <script src="<?= $link->asset('js/admin-messages.js') ?>" defer></script>

</head>
<body>
<div class="container-fluid mt-3">
    <div class="web-content">
        <?= $contentHTML ?>
    </div>
</div>

</body>
</html>
