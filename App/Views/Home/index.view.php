<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var array $boxesByKey */
/** @var bool $isAdmin */

$boxesByKey = $boxesByKey ?? [];
$isAdmin = $isAdmin ?? false;

$defaults = [
    'damske' => ['title' => 'Dámske strihy', 'description' => 'Od klasických po moderné účesy'],
    'panske' => ['title' => 'Pánske strihy', 'description' => 'Presné a štylizované strihy'],
    'farbenie' => ['title' => 'Farbenie', 'description' => 'Profesionálne farbenie vlasov'],
    'trvala' => ['title' => 'Trvalá', 'description' => 'Dlhotrvajúce kučery a vlny'],
    'melir' => ['title' => 'Melír', 'description' => 'Cez čiapku alebo fóliový'],
    'ucesy' => ['title' => 'Účesy na príležitosť', 'description' => 'Svadobné a slávnostné účesy'],
];

$getBox = function (string $key) use ($boxesByKey, $defaults) {
    $b = $boxesByKey[$key] ?? null;
    if ($b) {
        return ['title' => (string)$b->title, 'description' => (string)$b->description];
    }
    return $defaults[$key] ?? ['title' => $key, 'description' => ''];
};
?>

<div class="container-fluid">
    <div class="row">
        <div class="col mt-5">
            <div class="text-center">
                <h1>Kaderníctvo LUXER</h1>
                <h2>Profesionálna starostlivosť o vaše vlasy v Banskej Bystrici</h2>

                <?php if ($isAdmin) { ?>
                    <div class="mt-3">
                        <a class="btn btn-sm btn-outline-primary" href="<?= $link->url('admin.homeBoxes') ?>">Upraviť úvodné okienka</a>
                    </div>
                <?php } ?>

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
            <?php $b = $getBox('damske'); ?>
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/woman-hair.png') ?>" alt="Dámske strihy ikona">
                <h3><?= htmlspecialchars($b['title']) ?></h3>
                <p class="service-desc"><?= htmlspecialchars($b['description']) ?></p>
            </div>

            <?php $b = $getBox('panske'); ?>
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/man-hair.png') ?>" alt="Pánske strihy ikona">
                <h3><?= htmlspecialchars($b['title']) ?></h3>
                <p class="service-desc"><?= htmlspecialchars($b['description']) ?></p>
            </div>

            <?php $b = $getBox('farbenie'); ?>
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/hair-dye.png') ?>" alt="Farbenie ikona">
                <h3><?= htmlspecialchars($b['title']) ?></h3>
                <p class="service-desc"><?= htmlspecialchars($b['description']) ?></p>
            </div>
        </div>

        <div class="row boxes-row">
            <?php $b = $getBox('trvala'); ?>
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/long.png') ?>" alt="Trvalá ikona">
                <h3><?= htmlspecialchars($b['title']) ?></h3>
                <p class="service-desc"><?= htmlspecialchars($b['description']) ?></p>
            </div>

            <?php $b = $getBox('melir'); ?>
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/shampoo.png') ?>" alt="Melír ikona">
                <h3><?= htmlspecialchars($b['title']) ?></h3>
                <p class="service-desc"><?= htmlspecialchars($b['description']) ?></p>
            </div>

            <?php $b = $getBox('ucesy'); ?>
            <div class="box">
                <img class="service-icon" src="<?= $link->asset('images/hair.png') ?>" alt="Účesy na príležitosť ikona">
                <h3><?= htmlspecialchars($b['title']) ?></h3>
                <p class="service-desc"><?= htmlspecialchars($b['description']) ?></p>
            </div>
        </div>
    </div>
</div>
