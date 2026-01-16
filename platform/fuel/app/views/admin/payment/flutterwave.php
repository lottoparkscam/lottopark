<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $flutterwave_payment = new Forms_Whitelabel_Payment_Flutterwave();
    $flutterwave = $flutterwave_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsFlutterwave" class="payment-details hidden">
    <h3><?= _("Flutterwave integration details"); ?></h3>
    
    <div class="form-group <?= $flutterwave['public_key_error_class']; ?>">
        <label class="control-label" for="inputFlutterwavePublicKey">
            <?= _("Flutterwave Public Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $flutterwave['public_key_value']; ?>"
               class="form-control"
               id="inputFlutterwavePublicKey" 
               name="input[flutterwave_public_key]" 
               placeholder="<?= _("Enter Public Key"); ?>">
        <p class="help-block">
            <?= $flutterwave['public_key_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $flutterwave['secret_key_error_class']; ?>">
        <label class="control-label" for="inputFlutterwaveSecretKey">
            <?= _("Flutterwave Security Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $flutterwave['secret_key_value']; ?>"
               class="form-control"
               id="inputFlutterwaveSecretKey" 
               name="input[flutterwave_secret_key]" 
               placeholder="<?= _("Enter Flutterwave Secret Key"); ?>">
        <p class="help-block">
            <?= $flutterwave['secret_key_info']; ?>
        </p>
    </div>

    <div class="form-group <?= $flutterwave['secret_webhook_key_error_class']; ?>">
        <label class="control-label" for="inputFlutterwaveSecretWebhookKey">
            <?= _("Flutterwave Webhook Secret Key"); ?>:
        </label>
        <input type="text"
               value="<?= $flutterwave['secret_webhook_key_value']; ?>"
               class="form-control"
               id="inputFlutterwaveSecretWebhookKey"
               name="input[flutterwave_secret_webhook_key]"
               placeholder="<?= _("Enter Flutterwave Secret Webhook Key"); ?>">
        <p class="help-block">
            <?= $flutterwave['secret_webhook_key_info']; ?>
        </p>
    </div>

    <div class="form-group <?= $flutterwave['payment_options_error_class']; ?>">
        <label class="control-label" for="inputFlutterwavePaymentOptions">
            <?= _("Flutterwave payment options"); ?>:
        </label>
        <input type="text"
               value="<?= $flutterwave['payment_options_value']; ?>"
               class="form-control" 
               id="inputFlutterwavePaymentOptions"
               name="input[flutterwave_payment_options]"
               placeholder="<?= _("Enter Flutterwave Payment Options"); ?>">
        <p class="help-block">
            <?= $flutterwave['payment_options_info']; ?>
        </p>
    </div>

    <div class="form-group <?= $flutterwave['network_error_class']; ?>">
        <label class="control-label" for="inputFlutterwaveNetwork">
            <?= _("Flutterwave Network"); ?>:
        </label>
        <input type="text"
               value="<?= $flutterwave['network_value']; ?>"
               class="form-control"
               id="inputFlutterwaveNetwork"
               name="input[flutterwave_network]"
               placeholder="<?= _("Enter Flutterwave Network"); ?>">
        <p class="help-block">
            <?= $flutterwave['network_info']; ?>
        </p>
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[flutterwave_test]" 
                   value="1" <?= $flutterwave['test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test account."); ?>
        </p>
    </div>
</div>