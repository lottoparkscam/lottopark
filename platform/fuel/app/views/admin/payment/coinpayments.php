<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $coinpayments_payment = new Forms_Whitelabel_Payment_Coinpayments();
    $coinpayments = $coinpayments_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsCoinPayments" class="payment-details hidden">
    <h3>
        <?= _("CoinPayments integration details"); ?>
    </h3>
    
    <div class="form-group <?= $coinpayments['merchantid_error_class']; ?>">
        <label class="control-label" for="inputMerchantID">
            <?= _("Merchant ID"); ?>:
        </label>
        <input type="text" 
               value="<?= $coinpayments['merchantid_value']; ?>" 
               class="form-control" 
               id="inputMerchantID" 
               name="input[cpmerchantid]" 
               placeholder="<?= _("Enter Merchant ID"); ?>">
        <p class="help-block">
            <?= $coinpayments['merchantid_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $coinpayments['ipn_secret_error_class']; ?>">
        <label class="control-label" for="inputIPNSecret">
            <?= _("IPN Secret"); ?>:
        </label>
        <input type="text" 
               value="<?= $coinpayments['ipn_secret_value']; ?>" 
               class="form-control" 
               id="inputIPNSecret" 
               name="input[ipnsecret]" 
               placeholder="<?= _("Enter IPN Secret"); ?>">
        <p class="help-block">
            <?= $coinpayments['ipn_secret_info']; ?>
        </p>
    </div>
</div>