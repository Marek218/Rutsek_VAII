<?php

/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col mt-5">
            <div class="text-center">
                <h1>Vitajte v kaderníctve Luxor</h1>

            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col text-center">
        <button class="btn" onclick="location.href='<?= $link->url('home.contact') ?>'">
            Objednaj sa teraz
        </button>
        </div>
    </div>
</div>
