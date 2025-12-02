<?php

/** @var \Framework\Support\LinkGenerator $link */
?>

<!-- Order page view: simple booking form for salon appointments -->
<div class="row">
    <div class="col">
        <h1>
            Objednanie
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <!-- booking form: posts data to home.orderSubmit route for server-side processing -->
        <form class="order-form" method="post" action="<?= $link->url('home.orderSubmit') ?>">
            <div class="row">
                <!-- first name / last name -->
                <div class="col-md-6 mb-3">
                    <label for="first_name">Meno</label>
                    <!-- text input: customer's first name (required) -->
                    <input id="first_name" name="first_name" type="text" required class="form-control"
                           placeholder="Jana">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name">Priezvisko</label>
                    <!-- text input: customer's last name (required) -->
                    <input id="last_name" name="last_name" type="text" required class="form-control"
                           placeholder="Nováková">
                </div>
            </div>

            <div class="row">
                <!-- contact: email and phone -->
                <div class="col-md-6 mb-3">
                    <label for="email">Email</label>
                    <!-- email input with basic HTML validation -->
                    <input id="email" name="email" type="email" required class="form-control"
                           placeholder="meno@priklad.sk">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone">Telefón</label>
                    <!-- telephone input: pattern allows +, numbers, spaces, parentheses and hyphens -->
                    <input id="phone" name="phone" type="tel" required class="form-control"
                           placeholder="+421 900 000 000" pattern="[+0-9 ()-]{6,20}">
                </div>
            </div>

            <div class="row">
                <!-- service selection + date/time -->
                <div class="col-md-6 mb-3">
                    <label for="service">Služba</label>
                    <!-- select: choose the requested service (required) -->
                    <select id="service" name="service" class="form-select" required>
                        <option value="">Vyberte službu</option>
                        <option value="damske">Dámske strihy</option>
                        <option value="panske">Pánske strihy</option>
                        <option value="farbenie">Farbenie</option>
                        <option value="trvala">Trvalá</option>
                        <option value="melir">Melír</option>
                        <option value="ucesy">Účesy na príležitosť</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="date">Dátum</label>
                    <!-- date input: min set to today's date to prevent past bookings -->
                    <input id="date" name="date" type="date" required class="form-control"
                           min="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="time">Čas</label>
                    <!-- time input: 15-minute steps (step=900) -->
                    <input id="time" name="time" type="time" required class="form-control" step="900">
                </div>
            </div>

            <div class="mb-3">
                <label for="notes">Poznámka (voliteľné)</label>
                <!-- textarea for optional notes (preferences, allergies, etc.) -->
                <textarea id="notes" name="notes" class="form-control" rows="3"
                          placeholder="Stručné informácie, napr. preferovaný kaderník alebo alergie"></textarea>
            </div>

            <div class="text-center mt-4">
                <!-- submit button: sends the booking -->
                <button type="submit" class="btn btn-primary btn-lg">Poslať objednávku</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <!-- back link -->
            <a href="<?= $link->url("home.index") ?>">Späť na hlavnú stránku</a>
        </div>
    </div>
</div>
