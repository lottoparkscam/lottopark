<?php
if (empty($banks_ap)):
?>
    <div id="astro-pay-unsupported-message" class="platform-alert platform-alert-warning">
        <p>
            <span class="fa fa-exclamation-circle"></span>
            <?= Security::htmlentities(_('This payment method is not supported in your country.'))?>
        </p>
    </div>
<?php
else:
?>        
    <div class="payment-cc-content">
        <div class="form-group <?= $has_error_ap('national_id'); ?>">
            <label for="astro-payNationalId"
                class="payment-cc-content-field">
                <?= Security::htmlentities(_("National ID")); ?>:
            </label>
            <input type="text"
                    id="astro-payNationalId"
                    name="astro-pay[national_id]"
                    value="<?= $last_value_ap('national_id'); ?>">
            <p class="help-block w-50-1221-gt ml-50-important-1221-gt">
                <?= Security::htmlentities(_("Brazil: CPF/CNPJ, Argentina: DNI, Uruguay: CI, Mexico: CURP/RFC/IFE, Peru: DNI, Chile: RUT, Colombia: CC")) ?>
            </p>
        </div>
        <div class="form-group <?= $has_error_ap('bank_code'); ?>">
            <label for="astro-payBankCode"
                class="payment-cc-content-field">
                <?= Security::htmlentities(_("Bank")); ?>:
            </label>
            <select name="astro-pay[bank_code]"
                    id="astro-payBankCode" 
                    class="payment-selector">
                <?php
                    foreach ($banks_ap as $bank_code => $bank):
                ?>
                        <option value="<?= htmlspecialchars($bank_code) ?>" 
                                <?= $selected_ap('bank_code', $bank_code) ?>>
                            <?= Security::htmlentities($bank) ?>
                        </option>
                <?php
                    endforeach;
                ?>
            </select>
        </div>
        <div class="form-group <?= $has_error_ap('name') ?>">
            <label for="astro-payName"
                class="payment-cc-content-field">
                <?= Security::htmlentities(_("Name")); ?>:
            </label>
            <input type="text"
                id="astro-payName"
                name="astro-pay[name]"
                value="<?= $last_value_ap('name'); ?>">
        </div>
        <div class="form-group <?= $has_error_ap('surname') ?>">
            <label for="astro-paySurname"
                class="payment-cc-content-field">
                <?= Security::htmlentities(_("Surname")); ?>:
            </label>
            <input type="text"
                id="astro-paySurname"
                name="astro-pay[surname]"
                value="<?= $last_value_ap('surname'); ?>">
        </div>
    </div>
<?php
endif;
