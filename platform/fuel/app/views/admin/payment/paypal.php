<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $paypal_payment = new Forms_Whitelabel_Payment_Paypal();
    $paypal = $paypal_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsPaypal" class="payment-details hidden">
    <h3>
        <?= _("PayPal integration details"); ?>
    </h3>
    
    <div class="form-group <?= $paypal['logourl_error_class']; ?>">
        <label class="control-label" for="inputPayPalUrlLogo">
            <?= _("Logo url (optional)"); ?>:
        </label>
        <input type="text" 
               value="<?= $paypal['logourl_value']; ?>" 
               class="form-control" 
               id="inputPayPalUrlLogo" 
               name="input[logo_url_paypal]" 
               placeholder="<?= _("Enter Logo url"); ?>">
        <p class="help-block">
            <?= $paypal['text_info_url']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $paypal['apiclientid_error_class']; ?>">
        <label class="control-label" for="api_client_id_paypal">
            <?= _("API Client ID"); ?>:
        </label>
        <input type="text" 
               value="<?= $paypal['apiclientid_value']; ?>" 
               class="form-control" 
               id="api_client_id_paypal" 
               name="input[api_client_id_paypal]" 
               placeholder="<?= _("API Client ID"); ?>">
    </div>
    
    <div class="form-group <?= $paypal['apiclientsecret_error_class']; ?>">
        <label class="control-label" for="api_client_secret_paypal">
            <?= _("API Client Secret"); ?>:
        </label>
        <input type="text" 
               value="<?= $paypal['apiclientsecret_value']; ?>" 
               class="form-control" 
               id="api_client_secret_paypal" 
               name="input[api_client_secret_paypal]" 
               placeholder="<?= _("API Client Secret"); ?>">
    </div>
    
    <p class="help-block">
        <?= $paypal['text_info_1']; ?>
    </p>

    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[paypaltest]" 
                   value="1" 
                   <?= $paypal['test_checked']; ?>>
                <?= _("SandBox environment"); ?>
            <small>
                (<?= $paypal['text_info_2']; ?>)
            </small>
        </label>
    </div>
    
    <p class="help-block">
        <?= $paypal['text_info_3']; ?>
    </p>
    
    <p class="help-block">
        <?= $paypal['text_info_4']; ?>
    </p>
    
    <p class="help-block">
        <?= $paypal['text_info_5']; ?>
    </p>
</div>
