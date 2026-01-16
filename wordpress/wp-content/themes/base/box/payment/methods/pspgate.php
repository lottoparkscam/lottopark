<?php
    $trimmedFieldName = str_replace('pspgate.', '', Validator_Wordpress_Payments_PspGate::NAME_FIELD);
    $trimmedFieldSurname = str_replace('pspgate.', '', Validator_Wordpress_Payments_PspGate::SURNAME_FIELD);
?>

<div class="payment-cc-content">
    <div class="form-group <?= $has_error_pspgate($trimmedFieldName) ?>">
        <label for="pspgate-name"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("First Name")); ?>:
        </label>
        <input type="text"
               id="pspgate-name"
               name="pspgate[<?= $trimmedFieldName ?>]"
               value="<?= $last_value_pspgate($trimmedFieldName); ?>">
    </div>
    <div class="form-group <?= $has_error_pspgate($trimmedFieldSurname) ?>">
        <label for="pspgate-surname"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("Last Name")); ?>:
        </label>
        <input type="text"
               id="pspgate-surname"
               name="pspgate[<?= $trimmedFieldSurname ?>]"
               value="<?= $last_value_pspgate($trimmedFieldSurname); ?>">
    </div>
</div>
