<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $bhartipay = new Forms_Whitelabel_Payment_Bhartipay();
    $bhartipay_data = $bhartipay->prepare_data_to_show($data);
?>
<div id="paymentDetailsBhartipay" class="payment-details hidden">
    <h3><?= _("Bhartipay integration details"); ?></h3>

    <div class="form-group">
        <label class="control-label" for="inputBhartipayPayId">
            <?= _("Bhartipay Payload Id"); ?>:
        </label>
        <input type="text" 
               value="<?= $bhartipay_data['bhartipay_pay_id'] ?>" 
               class="form-control" 
               id="inputVisaNetUser" 
               name="input[bhartipay_pay_id]" 
               placeholder="<?= _("Enter payload id"); ?>">
    </div>
    
    <div class="form-group">
        <label class="control-label" for="inputBhartipaySecretKey">
            <?= _("Bhartipay Secret key (Salt)"); ?>:
        </label>
        <input type="text" 
               value="<?= $bhartipay_data['bhartipay_secret_key'] ?>" 
               class="form-control" 
               id="inputBhartipaySecretKey" 
               name="input[bhartipay_secret_key]" 
               placeholder="<?= _("Enter secret key (salt)"); ?>">
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[bhartipay_test]" 
                   value="1"<?= $bhartipay_data['bhartipay_test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test account."); ?>
        </p>
    </div>
</div>