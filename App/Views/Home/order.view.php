<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
// Possible variables passed from controller: $errors (array), $old (array), $error (string)
$errors = $errors ?? [];
$old = $old ?? [];
$error = $error ?? null;

?>

<!-- Order page view: simple booking form for salon appointments -->
<div class="row">
    <div class="col">
        <h1>Objednanie</h1>
        <p class="text-muted mb-0">Vyplňte formulár a my sa vám ozveme s potvrdením termínu.</p>
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
        <!-- booking form: posts data to home.orderSubmit route for server-side processing -->
        <form class="order-form" method="post" action="<?= $link->url('order.submit') ?>">
            <div class="row">
                <!-- first name / last name -->
                <div class="col-md-6 mb-3">
                    <label for="first_name">Meno</label>
                    <!-- text input: customer's first name -->
                    <input id="first_name" name="first_name" type="text" required class="form-control"
                           placeholder="Jana" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>">
                    <?php if (isset($errors['first_name'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['first_name']) ?></div>
                    <?php } ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name">Priezvisko</label>
                    <!-- text input: customer's last name (required) -->
                    <input id="last_name" name="last_name" type="text" required class="form-control"
                           placeholder="Nováková" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>">
                    <?php if (isset($errors['last_name'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['last_name']) ?></div>
                    <?php } ?>
                </div>
            </div>

            <div class="row">
                <!-- contact: email and phone -->
                <div class="col-md-6 mb-3">
                    <label for="email">Email</label>
                    <!-- email input with basic HTML validation -->
                    <input id="email" name="email" type="email" required class="form-control"
                           placeholder="meno@priklad.sk" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                    <?php if (isset($errors['email'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php } ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone">Telefón</label>
                    <!-- telephone input: pattern allows +, numbers, spaces, parentheses and hyphens -->
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
                    <label for="service">Služba</label>
                    <!-- select: choose the requested service (required) -->
                    <select id="service" name="service" class="form-select" required>
                        <option value="">Vyberte službu</option>
                        <?php
                        $svcOld = $old['service'] ?? '';
                        $options = [
                            'damske' => 'Dámske strihy',
                            'panske' => 'Pánske strihy',
                            'farbenie' => 'Farbenie',
                            'trvala' => 'Trvalá',
                            'melir' => 'Melír',
                            'ucesy' => 'Účesy na príležitosť'
                        ];
                        foreach ($options as $val => $label) {
                            $sel = $svcOld === $val ? ' selected' : '';
                            echo "<option value=\"" . htmlspecialchars($val) . "\"$sel>" . htmlspecialchars($label) . "</option>";
                        }
                        ?>
                    </select>
                    <?php if (isset($errors['service'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['service']) ?></div>
                    <?php } ?>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="date">Dátum</label>
                    <!-- date input: min set to today's date to prevent past bookings -->
                    <input id="date" name="date" type="date" required class="form-control"
                           min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($old['date'] ?? '') ?>">
                    <?php if (isset($errors['date'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['date']) ?></div>
                    <?php } ?>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="time">Čas</label>
                    <!-- time input: 15-minute steps (step=900) -->
                    <input id="time" name="time" type="time" required class="form-control" step="900" value="<?= htmlspecialchars($old['time'] ?? '') ?>">
                    <?php if (isset($errors['time'])) { ?>
                        <div class="form-text text-danger"><?= htmlspecialchars($errors['time']) ?></div>
                    <?php } ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="notes">Poznámka (voliteľné)</label>
                <!-- textarea for optional notes (preferences, allergies, etc.) -->
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Stručné informácie, napr. preferovaný kaderník alebo alergie"><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
            </div>

            <div class="text-center mt-4">
                <!-- submit button: sends the booking -->
                <button type="submit" class="btn btn-primary btn-lg">Poslať objednávku</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <!-- back link -->
            <a href="<?= $link->url('order.index') ?>">Späť na objednávky</a>
        </div>
    </div>
</div>
