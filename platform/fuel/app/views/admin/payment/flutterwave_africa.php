<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $flutterwave_africa_payment = new Forms_Whitelabel_Payment_FlutterwaveAfrica();
    $flutterwave_africa = $flutterwave_africa_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsFlutterwaveAfrica" class="payment-details hidden">
    <h3><?= _("Flutterwave integration details"); ?></h3>
    
    <div class="form-group <?= $flutterwave_africa['public_key_error_class']; ?>">
        <label class="control-label" for="inputFlutterwaveAfricaPublicKey">
            <?= _("Flutterwave Public Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $flutterwave_africa['public_key_value']; ?>"
               class="form-control"
               id="inputFlutterwaveAfricaPublicKey"
               name="input[flutterwave_africa_public_key]"
               placeholder="<?= _("Enter Public Key"); ?>">
        <p class="help-block">
            <?= $flutterwave_africa['public_key_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $flutterwave_africa['secret_key_error_class']; ?>">
        <label class="control-label" for="inputFlutterwaveAfricaSecretKey">
            <?= _("Flutterwave Security Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $flutterwave_africa['secret_key_value']; ?>"
               class="form-control"
               id="inputFlutterwaveAfricaSecretKey"
               name="input[flutterwave_africa_secret_key]"
               placeholder="<?= _("Enter Flutterwave Secret Key"); ?>">
        <p class="help-block">
            <?= $flutterwave_africa['secret_key_info']; ?>
        </p>
    </div>

    <div class="form-group <?= $flutterwave_africa['secret_webhook_key_error_class']; ?>">
        <label class="control-label" for="inputFlutterwaveAfricaSecretWebhookKey">
            <?= _("Flutterwave Webhook Secret Key"); ?>:
        </label>
        <input type="text"
               value="<?= $flutterwave_africa['secret_webhook_key_value']; ?>"
               class="form-control"
               id="inputFlutterwaveAfricaSecretWebhookKey"
               name="input[flutterwave_africa_secret_webhook_key]"
               placeholder="<?= _("Enter Flutterwave Secret Webhook Key"); ?>">
        <p class="help-block">
            <?= $flutterwave_africa['secret_webhook_key_info']; ?>
        </p>
    </div>

    <div class="form-group <?= $flutterwave_africa['payment_options_error_class']; ?>">
        <label class="control-label" for="inputFlutterwaveAfricaPaymentOptions">
            <?= _("Flutterwave payment options"); ?>:
        </label>
        <input type="text"
               value="<?= $flutterwave_africa['payment_options_value']; ?>"
               class="form-control" 
               id="inputFlutterwaveAfricaPaymentOptions"
               name="input[flutterwave_africa_payment_options]"
               placeholder="<?= _("Enter Flutterwave Payment Options"); ?>">
        <p class="help-block">
            <?= $flutterwave_africa['payment_options_info']; ?>
        </p>
    </div>

    <div class="form-group <?= $flutterwave_africa['network_error_class']; ?>">
        <label class="control-label" for="inputFlutterwaveAfricaNetwork">
            <?= _("Flutterwave Network"); ?>:
        </label>
        <input type="text"
               value="<?= $flutterwave_africa['network_value']; ?>"
               class="form-control"
               id="inputFlutterwaveAfricaNetwork"
               name="input[flutterwave_africa_network]"
               placeholder="<?= _("Enter Flutterwave Network"); ?>">
        <p class="help-block">
            <?= $flutterwave_africa['network_info']; ?>
        </p>
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[flutterwave_africa_test]"
                   value="1" <?= $flutterwave_africa['test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test account."); ?>
        </p>
    </div>
</div>