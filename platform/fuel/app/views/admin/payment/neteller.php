<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $neteller_payment = new Forms_Whitelabel_Payment_Neteller();
    $neteller_payment->set_whitelabel($whitelabel);
    $neteller = $neteller_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsNeteller" class="payment-details hidden">
    <h3>
        <?= _("Neteller integration details"); ?>
    </h3>
    
    <div class="form-group <?= $neteller['appclientid_error_class']; ?>">
        <label class="control-label" for="inputAppClientID">
            <?= _("App Client ID"); ?>:
        </label>
        <input type="text" 
               value="<?= $neteller['appclientid_value']; ?>" 
               class="form-control" 
               id="inputAppClientID" 
               name="input[appclientid]" 
               placeholder="<?= _("Enter App Client ID"); ?>">
    </div>
    
    <div class="form-group <?= $neteller['appclientsecret_error_class']; ?>">
        <label class="control-label" for="inputAppClientSecret">
            <?= _("Enter App Client Secret"); ?>:
        </label>
        <input type="text" 
               value="<?= $neteller['appclientsecret_value']; ?>" 
               class="form-control" 
               id="inputAppClientSecret" 
               name="input[appclientsecret]" 
               placeholder="<?= _("Enter App Client Secret"); ?>">
        <p class="help-block">
            <?= $neteller['appclientsecret_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $neteller['webhooksecretkey_error_class']; ?>">
        <label class="control-label" for="inputWebhookSecretKey">
            <?= _("Webhook Secret Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $neteller['webhooksecretkey_value']; ?>" 
               class="form-control" 
               id="inputWebhookSecretKey" 
               name="input[webhooksecretkey]" 
               placeholder="<?= _("Enter Webhook Secret Key"); ?>">
        <p class="help-block">
            <?= $neteller['help_text']; ?>
        </p>
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[test]" 
                   value="1"<?= $neteller['test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test account."); ?>
        </p>
    </div>
</div>

