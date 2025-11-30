<?php

/** @var \Framework\Support\LinkGenerator $link */
?>

<link rel="stylesheet" href="<?= $link->asset('css/services.css') ?>?v=<?= time() ?>">
<div class="row">
    <div class="col">
        <h1>
            O nás
        </h1>
    </div>
</div>
<div class="rect-grid">
    <div class="rect">TU SA BUDES MOCT OBJEDNAT</div>
</div>
<div class="row mt-3">
    <div class="col">
        <a href="<?= $link->url("home.index") ?>">Späť na hlavnú stránku</a>
    </div>
</div>
