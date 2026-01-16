<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $sepa_payment = new Forms_Whitelabel_Payment_Sepa();
    $sepa = $sepa_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsSepa" class="payment-details hidden">
    <h3><?= _("Sepa integration details"); ?></h3>

    <div class="form-group <?= $sepa['sepa_member_id_error_class']; ?>">
        <label class="control-label" for="inputMemberId">
            <?= _("Merchant ID"); ?>:
        </label>
        <input type="text" 
               value="<?= $sepa['sepa_member_id_value']; ?>" 
               class="form-control" 
               id="inputMemberId" 
               name="input[sepa_member_id]" 
               placeholder="<?= _("Enter Merchant's ID"); ?>">
        <p class="help-block">
            <?= $sepa['sepa_member_id_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $sepa['sepa_secure_key_error_class']; ?>">
        <label class="control-label" for="inputSepaSecureKey">
            <?= _("Secure Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $sepa['sepa_secure_key_value']; ?>" 
               class="form-control" 
               id="inputSepaSecureKey" 
               name="input[sepa_secure_key]" 
               placeholder="<?= _("Secure Key"); ?>">
        <p class="help-block">
            <?= $sepa['sepa_secure_key_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $sepa['sepa_to_type_error_class']; ?>">
        <label class="control-label" for="inputToType">
            <?= _("Merchant's Partner name"); ?>:
        </label>
        <input type="text" 
               value="<?= $sepa['sepa_to_type_value']; ?>" 
               class="form-control" 
               id="inputToType" 
               name="input[sepa_to_type]" 
               placeholder="<?= _("Enter Merchant's Partner name"); ?>">
        <p class="help-block">
            <?= $sepa['sepa_to_type_info']; ?>
        </p>
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[sepa_test]" 
                   value="1" 
                   <?= $sepa['test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test account."); ?>
        </p>
    </div>
</div>
