<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $ecopayz_payment = new Forms_Whitelabel_Payment_Ecopayz();
    $ecopayz = $ecopayz_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsEcoPayz" class="payment-details hidden">
    <h3>
        <?= _("EcoPayz integration details"); ?>
    </h3>
    
    <div class="form-group <?= $ecopayz['merchantid_error_class']; ?>">
        <label class="control-label" for="inputMerchantID">
            <?= _("Merchant ID"); ?>:
        </label>
        <input type="number" 
               value="<?= $ecopayz['merchantid_value']; ?>" 
               class="form-control" 
               id="inputMerchantID" 
               name="input[merchantid]" 
               placeholder="<?= _("Enter Merchant ID"); ?>">
        <p class="help-block">
            <?= _("Provided by ecoPayz."); ?>
        </p>
    </div>
    
    <div class="form-group <?= $ecopayz['account_error_class']; ?>">
        <label class="control-label" for="inputAccount">
            <?= _("Merchant Account Number"); ?>:
        </label>
        <input type="number" 
               value="<?= $ecopayz['account_value']; ?>" 
               class="form-control" 
               id="inputAccount" 
               name="input[account]" 
               placeholder="<?= _("Enter Merchant Account Number"); ?>">
        <p class="help-block">
            <?= _("Provided by ecoPayz."); ?>
        </p>
    </div>
    
    <div class="form-group <?= $ecopayz['password_error_class']; ?>">
        <label class="control-label" for="inputPassword">
            <?= _("Merchant Password"); ?>:
        </label>
        <input type="text" 
               value="<?= $ecopayz['password_value']; ?>" 
               class="form-control" 
               id="inputPassword" 
               name="input[password]" 
               placeholder="<?= _("Enter Merchant Password"); ?>">
        <p class="help-block">
            <?= _("Provided by ecoPayz."); ?>
        </p>
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[ecotest]"
                   value="1"<?= $ecopayz['test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test account."); ?>
        </p>
    </div>
</div>