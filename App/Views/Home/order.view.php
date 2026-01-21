<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
/** @var \App\Models\Service[] $services */
// Possible variables passed from controller: $errors (array), $old (array), $error (string)
$errors = $errors ?? [];
$old = $old ?? [];
$error = $error ?? null;
$services = $services ?? [];

?>

<!-- Order page view: simple booking form for salon appointments -->
<div class="row">
    <div class="col">
        <h1>Objednanie</h1>
    </div>
</div>

<?php if ($error) { ?>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="alert alert-danger">Chyba pri uložení: <?= htmlspecialchars($error) ?></div>
        </div>
    </div>
<?php } ?>

<?php if (!empty($errors)) { ?>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="alert alert-warning">
                <strong>Opravte prosím nasledujúce chyby:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $k => $v) { echo '<li>' . htmlspecialchars($v) . '</li>'; } ?>
                </ul>
            </div>
        </div>
    </div>
<?php } ?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <!-- booking form: sends data to OrderController::submit (order.submit) -->
        <form class="order-form" method="post" action="<?= $link->url('order.submit') ?>"
              data-availability-url="<?= $link->url('order.availability') ?>"
              data-availability-url-fallback="/order/availability"
              data-next-available-url="<?= $link->url('order.nextAvailable') ?>"
              data-next-available-url-fallback="/order/nextAvailable">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name">Meno</label>
                    <input id="first_name" name="first_name" type="text" required class="form-control"
                           placeholder="Jana" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>">
                    <?php if (isset($errors['first_name'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['first_name']) ?></div>
                    <?php } ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name">Priezvisko</label>
                    <input id="last_name" name="last_name" type="text" required class="form-control"
                           placeholder="Nováková" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>">
                    <?php if (isset($errors['last_name'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['last_name']) ?></div>
                    <?php } ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" required class="form-control"
                           placeholder="meno@priklad.sk" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                    <?php if (isset($errors['email'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php } ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone">Telefón</label>
                    <input id="phone" name="phone" type="tel" required class="form-control"
                           placeholder="+421 900 000 000" pattern="[+0-9 ()-]{6,20}" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                    <?php if (isset($errors['phone'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['phone']) ?></div>
                    <?php } ?>
                </div>
            </div>

            <div class="row">
                <!-- service selection + date/time -->
                <div class="col-md-6 mb-3">
                    <label for="service_id">Služba</label>
                    <select id="service_id" name="service_id" class="form-select" required>
                        <option value="">Vyberte službu</option>
                        <?php
                        $svcOld = (string)($old['service_id'] ?? '');
                        foreach ($services as $svc) {
                            $id = (int)($svc->id ?? 0);
                            if ($id <= 0) { continue; }
                            $sel = ($svcOld !== '' && (int)$svcOld === $id) ? ' selected' : '';
                            $label = (string)($svc->name ?? '');
                            echo "<option value=\"{$id}\"{$sel}>" . htmlspecialchars($label) . "</option>";
                        }
                        ?>
                    </select>
                    <?php if (isset($errors['service_id'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['service_id']) ?></div>
                    <?php } ?>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="date">Dátum</label>
                    <input id="date" name="date" type="date" required class="form-control"
                           min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($old['date'] ?? '') ?>">
                    <?php if (isset($errors['date'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['date']) ?></div>
                    <?php } ?>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="time">Čas</label>
                    <input id="time" name="time" type="time" required class="form-control" step="900" value="<?= htmlspecialchars($old['time'] ?? '') ?>">
                    <?php if (isset($errors['time'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['time']) ?></div>
                    <?php } ?>
                    <div id="availabilityStatus" class="form-text"></div>
                    <div id="nextAvailableStatus" class="form-text"></div>
                </div>
            </div>

            <div class="mb-3">
                <label for="notes">Poznámka (voliteľné)</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Stručné informácie"><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Poslať objednávku</button>
            </div>
        </form>
    </div>
</div>