<?php

/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col mt-5">
            <div class="text-center">
                <h1>Kaderníctvo LUXER</h1>
                <h2>Profesionálna starostlivosť o vaše vlasy v Banskej Bystrici</h2>

            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col text-center">
            <div class="action-row" role="group" aria-label="Call to action">
                <button class="btn" onclick="location.href='<?= $link->url('home.order') ?>'" aria-label="Objednaj sa teraz">
                    Objednaj sa teraz
                </button>

                <button class="btn" onclick="location.href='<?= $link->url('home.services') ?>'" aria-label="Pozrieť cenník">
                    Pozrieť cenník
                </button>
            </div>
        </div>

        <!-- Services title -->
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="services-title">Naše služby</h2>
            </div>
        </div>

        <div class="row boxes-row">
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/woman-hair.png') ?>" alt="Dámske strihy ikona">

                <h3>Dámske strihy</h3>
                <p class="service-desc">Od klasických po moderné účesy</p>
            </div>
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/woman-hair.png') ?>" alt="Pánske strihy ikona">
                <h3>Pánske strihy</h3>
                <p class="service-desc">Presné a štylizované strihy</p>
            </div>
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/woman-hair.png') ?>" alt="Farbenie ikona">
                <h3>Farbenie</h3>
                <p class="service-desc">Profesionálne farbenie vlasov</p>
            </div>
        </div>

        <div class="row boxes-row">
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/woman-hair.png') ?>" alt="Trvalá ikona">
                <h3>Trvalá</h3>
                <p class="service-desc">Dlhotrvajúce kučery a vlny</p>
            </div>
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/woman-hair.png') ?>" alt="Melír ikona">
                <h3>Melír</h3>
                <p class="service-desc">Cez čiapku alebo fóliový</p>
            </div>
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/woman-hair.png') ?>" alt="Účesy na príležitosť ikona">
                <h3>Účesy na príležitosť</h3>
                <p class="service-desc">Svadobné a slávnostné účesy</p>
            </div>
        </div>
    </div>
</div>
