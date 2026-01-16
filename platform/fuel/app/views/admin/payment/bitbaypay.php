<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $bitbaypay_payment = new Forms_Whitelabel_Payment_Bitbaypay();
    $bitbaypay = $bitbaypay_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsBitBayPay" class="payment-details hidden">
    <h3>
        <?= _("BitBayPay integration details"); ?>
    </h3>
    
    <div class="form-group <?= $bitbaypay['public_api_key_error_class']; ?>">
        <label class="control-label" for="inputBitBayPayId">
            <?= _("BitBayPay public API key"); ?>:
        </label>
        <input type="text" 
               value="<?= $bitbaypay['public_api_key_value']; ?>" 
               class="form-control" 
               id="inputBitBayPayId" 
               name="input[marchant_bitbaypay_public_api_key]" 
               placeholder="<?= _("Enter BitBayPay public API key"); ?>">
        <p class="help-block">
            <?= $bitbaypay['public_api_key_help_text']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $bitbaypay['private_api_key_error_class']; ?>">
        <label class="control-label" for="inputBitBayPayApiKey">
            <?= _("BitBayPay private API key"); ?>:
        </label>
        <input type="text" 
               value="<?= $bitbaypay['private_api_key_value']; ?>" 
               class="form-control" 
               id="inputBitBayPayApiKey" 
               name="input[marchant_bitbaypay_private_api_key]" 
               placeholder="<?= _("Enter BitBayPay private API key"); ?>">
        <p class="help-block">
            <?= $bitbaypay['private_api_key_help_text']; ?>
        </p>
    </div>
    
    <p class="help-block">
        <?= $bitbaypay['api_keys_help_text']; ?>
    </p>
</div>