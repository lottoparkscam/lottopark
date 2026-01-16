<?php
$errors_to_process = null;
if (!empty($errors)) {
    $errors_to_process = $errors;
}
$payop_payment = new Forms_Whitelabel_Payment_Payop();
$payop = $payop_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsPayOp" class="payment-details hidden">
    <h3>
        <?= _("PayOp integration details"); ?>
    </h3>
    
    <div class="form-group <?= $payop['publickey_error_class']; ?>">
        <label class="control-label" for="inputPayOpPublicKey">
            <?= _("PayOp Public Key") ?>:
        </label>
        <input type="text" 
               value="<?= $payop['publickey_value']; ?>"
               class="form-control"
               id="inputPayOpPublicKey"
               name="input[payop_public_key]"
               placeholder="<?= _("Enter PayOp Public Key") ?>">
        <p class="help-block">
            <?= _('Public Key from notifications settings.') ?>
        </p>
    </div>
    
    <div class="form-group <?= $payop['secretkey_error_class']; ?>">
        <label class="control-label" for="inputPayOpSecretKey">
            <?= _("PayOp Secret Key") ?>:
        </label>
        <input type="text" 
               value="<?= $payop['secretkey_value']; ?>"
               class="form-control"
               id="inputPayOpSecretKey"
               name="input[payop_secret_key]"
               placeholder="<?= _("Enter PayOp Secret Key") ?>">
        <p class="help-block">
            <?= _('Secret Key from notifications settings.') ?>
        </p>
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[payop_is_test]"
                   value="1" <?= $payop['test_checked'] ?>>
            <?= _("Test account") ?>
        </label>
        <p class="help-block"><?= _("Check it for test account") ?>.
    </div>
</div>
