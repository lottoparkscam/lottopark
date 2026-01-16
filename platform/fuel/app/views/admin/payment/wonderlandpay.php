<?php
$errors_to_process = null;
if (!empty($errors)) {
    $errors_to_process = $errors;
}
$wonderlandpay_payment = new Forms_Whitelabel_Payment_WonderlandPay();
$wonderlandpay = $wonderlandpay_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsWonderlandPay" class="payment-details hidden">
    <h3>
        <?= _("WonderlandPay integration details"); ?>
    </h3>

    <div class="form-group <?= $wonderlandpay['merchantnumber_error_class']; ?>">
        <label class="control-label" for="inputWonderlandPayMerchantNumber">
            <?= _("WonderlandPay Merchant Number") ?>:
        </label>
        <input type="text"
               value="<?= $wonderlandpay['merchantnumber_value']; ?>"
               class="form-control"
               id="inputWonderlandPayMerchantNumber"
               name="input[wonderlandpay_merchant_number]"
               placeholder="<?= _("Enter WonderlandPay Merchant Number") ?>">
        <p class="help-block">
            <?= _('Merchant Number from notifications settings.') ?>
        </p>
    </div>

    <div class="form-group <?= $wonderlandpay['gatewaynumber_error_class']; ?>">
        <label class="control-label" for="inputWonderlandPayGatewayNumber">
            <?= _("WonderlandPay Gateway Number") ?>:
        </label>
        <input type="text"
               value="<?= $wonderlandpay['gatewaynumber_value']; ?>"
               class="form-control"
               id="inputWonderlandPayGatewayNumber"
               name="input[wonderlandpay_gateway_number]"
               placeholder="<?= _("Enter WonderlandPay Gateway Number") ?>">
        <p class="help-block">
            <?= _('Gateway Number from notifications settings.') ?>
        </p>
    </div>

    <div class="form-group <?= $wonderlandpay['secretkey_error_class']; ?>">
        <label class="control-label" for="inputWonderlandPaySecretKey">
            <?= _("WonderlandPay Secret Key") ?>:
        </label>
        <input type="text"
               value="<?= $wonderlandpay['secretkey_value']; ?>"
               class="form-control"
               id="inputWonderlandPaySecretKey"
               name="input[wonderlandpay_secret_key]"
               placeholder="<?= _("Enter WonderlandPay Secret Key") ?>">
        <p class="help-block">
            <?= _('WonderlandPay Key from notifications settings.') ?>
        </p>
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[wonderlandpay_is_test]"
                   value="1" <?= $wonderlandpay['test_checked'] ?>>
            <?= _("Test account") ?>
        </label>
        <p class="help-block"><?= _("Check it for test account") ?>.
    </div>
</div>
