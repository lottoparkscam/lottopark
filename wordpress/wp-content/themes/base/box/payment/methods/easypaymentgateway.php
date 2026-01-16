<?php

// TODO: {Vordis 2019-05-23 09:34:03} ideally epg should have it's own style for additional fields form
// TODO: {Vordis 2019-06-03 16:17:54} unsure if should refractor nearly identical fields in astro-pay (name, surname, country).
// TODO: {Vordis 2019-06-03 15:48:24} Please fill up message probably should be parsed via sprintf for easier translation.
?>
<div class="payment-cc-content">
    <div class="form-group <?= $has_error_epg('national_id'); ?>">
        <label for="easy-payment-gatewayNationalId"
            class="payment-cc-content-field">
            <?= Security::htmlentities(_("National ID")); ?>:
        </label>
        <input type="text"
               id="easy-payment-gatewayNationalId"
               name="easy-payment-gateway[national_id]"
               value="<?= $last_value_epg('national_id'); ?>">
    </div>
    <div class="form-group <?= $has_error_epg('name') ?>">
            <label for="easy-payment-gatewayName"
                class="payment-cc-content-field">
                <?= Security::htmlentities(_("Name")); ?>:
            </label>
            <input type="text"
                id="easy-payment-gatewayName"
                name="easy-payment-gateway[name]"
                value="<?= $last_value_epg('name'); ?>">
    </div>
    <div class="form-group <?= $has_error_epg('surname') ?>">
        <label for="easy-payment-gatewaySurname"
            class="payment-cc-content-field">
            <?= Security::htmlentities(_("Surname")); ?>:
        </label>
        <input type="text"
            id="easy-payment-gatewaySurname"
            name="easy-payment-gateway[surname]"
            value="<?= $last_value_epg('surname'); ?>">
    </div>
    <div class="form-group <?= $has_error_epg('country_code'); ?>">
        <label for="easy-payment-gatewayCountryCode"
            class="payment-cc-content-field">
            <?= Security::htmlentities(_("Country")); ?>:
        </label>
        <select name="easy-payment-gateway[country_code]"
                id="easy-payment-gatewayCountryCode" 
                class="payment-selector">
                <option value="">
                    <?= Security::htmlentities(_("Choose your country")) ?>
                </option>
            <?php

                foreach (Lotto_Helper::get_localized_country_list() as $country_code => $country):
                    $selected = $selected_epg('country_code', $country_code) // if it's not found from input and user, than try broader scope
                        ?: (Lotto_Helper::get_best_match_user_country() === $country_code ? 'selected' : '');
            ?>
                    <option value="<?= htmlspecialchars($country_code) ?>" <?= $selected ?>>
                        <?= Security::htmlentities($country) ?>
                    </option>
            <?php
                endforeach;
            ?>
        </select>
    </div>
</div>
