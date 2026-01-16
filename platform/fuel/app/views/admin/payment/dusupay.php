<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $dusupay_payment = new Forms_Whitelabel_Payment_Dusupay();
    $dusupay = $dusupay_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsDusuPay" class="payment-details hidden">
    <h3>
        <?= _("DusuPay integration details"); ?>
    </h3>
    
    <div class="form-group <?= $dusupay['merchantid_error_class']; ?>">
        <label class="control-label" for="inputDusuPayMerchantId">
            <?= _("DusuPay Merchant ID"); ?>:
        </label>
        <input type="text" 
               value="<?= $dusupay['merchantid_value']; ?>" 
               class="form-control" 
               id="inputDusuPayMerchantId" 
               name="input[merchant_dusupay_id]" 
               placeholder="<?= _("Enter DusuPay Merchant ID"); ?>">
        <p class="help-block">
            <?= $dusupay['merchantid_help_text']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $dusupay['apikey_error_class'] ?>">
        <label class="control-label" for="inputDusuPayApiKey">
            <?= _("DusuPay Mackey/Salt Key"); ?>
        </label>
        <input type="text" 
               value="<?= $dusupay['apikey_value']; ?>" 
               class="form-control" 
               id="inputDusuPayApiKey" 
               name="input[merchant_dusupay_apikey]" 
               placeholder="<?= _("Enter DusuPay Mackey/Salt Key"); ?>">
        <p class="help-block">
            <?= $dusupay['apikey_help_text']; ?>
        </p>
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[dusupay_test]" 
                   value="1" <?= $dusupay['test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test account."); ?>
        </p>
    </div>
</div>