<?php
$trimmedFieldName = str_replace('zen.', '', Validator_Wordpress_Payments_Zen::NAME_FIELD);
$trimmedFieldSurname = str_replace('zen.', '', Validator_Wordpress_Payments_Zen::SURNAME_FIELD);
?>

<div class="payment-cc-content">
    <div class="form-group <?= $has_error_zen($trimmedFieldName) ?>">
        <label for="zen-name"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("First Name")); ?>:
        </label>
        <input type="text"
               id="zen-name"
               name="zen[<?= $trimmedFieldName ?>]"
               value="<?= $last_value_zen($trimmedFieldName); ?>">
    </div>
    <div class="form-group <?= $has_error_zen($trimmedFieldSurname) ?>">
        <label for="zen-surname"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("Last Name")); ?>:
        </label>
        <input type="text"
               id="zen-surname"
               name="zen[<?= $trimmedFieldSurname ?>]"
               value="<?= $last_value_zen($trimmedFieldSurname); ?>">
    </div>
</div>
