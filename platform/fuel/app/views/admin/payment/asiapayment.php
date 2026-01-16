<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $asiapayment_payment = new Forms_Whitelabel_Payment_Asiapayment();
    $asiapayment = $asiapayment_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsAsiapaymentgateway" class="payment-details hidden">
    <h3>
        <?= _("AsiaPaymentGateway integration details"); ?>
    </h3>
    
    <div class="form-group <?= $asiapayment['merchantid_error_class']; ?>">
        <label class="control-label" for="inputMerchantID">
            <?= _("Merchant ID"); ?>:
        </label>
        <input type="text" 
               value="<?= $asiapayment['merchantid_value']; ?>" 
               class="form-control" 
               id="inputMerchantID" 
               name="input[merchant_id_asiapayment]" 
               placeholder="<?= _("Enter Merchant ID"); ?>">
    </div>
    
    <div class="form-group <?= $asiapayment['sha256key_error_class']; ?>">
        <label class="control-label" for="inputSHA256key">
            <?= _("SHA256key"); ?>:
        </label>
        <input type="text" 
               value="<?= $asiapayment['sha256key_value']; ?>" 
               class="form-control" 
               id="inputSHA256key" 
               name="input[sha256key]" 
               placeholder="<?= _("Enter SHA256key"); ?>">
    </div>
    
    <div class="form-group <?= $asiapayment['apiurl_error_class']; ?>">
        <label class="control-label" for="inputApiurl">
            <?= _("API Url"); ?>:
        </label>
        <input type="text" 
               value="<?= $asiapayment['apiurl_value']; ?>" 
               class="form-control" 
               id="inputApiurl" 
               name="input[apiurl]" 
               placeholder="<?= _("Enter API Url"); ?>">
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[asiapaymenttest]" 
                   value="1" 
                   <?= $asiapayment['test_checked']; ?>>
                <?= _("Test environment"); ?>
        </label>
        <p class="help-block">
            <?= _("Use the amount of 0.01 if the merchant tests the API connection."); ?>
        </p>
    </div>
</div>
