<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $tpay_payment = new Forms_Whitelabel_Payment_Tpay();
    $tpay = $tpay_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsTpay" class="payment-details hidden">
    <h3><?= _("tpay.com integration details"); ?></h3>
    
    <div class="form-group <?= $tpay['id_error_class']; ?>">
        <label class="control-label" for="inputTpayID">
            <?= _("Merchant ID"); ?>:
        </label>
        <input type="text" 
               value="<?= $tpay['id_value']; ?>" 
               class="form-control" 
               id="inputTpayID" 
               name="input[tpayid]" 
               placeholder="<?= _("Enter tpay.com ID"); ?>">
    </div>
    
    <div class="form-group <?= $tpay['security_key_error_class']; ?>">
        <label class="control-label" for="inputTpaySecurityKey">
            <?= _("tpay.com Security Code"); ?>:
        </label>
        <input type="text" 
               value="<?= $tpay['security_key_value']; ?>" 
               class="form-control" 
               id="inputTpaySecurityKey" 
               name="input[tpaysecuritykey]" 
               placeholder="<?= _("Enter tpay.com Security Code"); ?>">
        <p class="help-block">
            <?= $tpay['security_key_info']; ?>
        </p>
    </div>
</div>