<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $stripe_payment = new Forms_Whitelabel_Payment_Stripe();
    $stripe_payment->set_whitelabel($whitelabel);
    $stripe = $stripe_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsStripe" class="payment-details hidden">
    <h3><?= _("Stripe integration details"); ?></h3>
    
    <div class="form-group <?= $stripe['publishable_key_error_class']; ?>">
        <label class="control-label" for="inputStripePublishableKey">
            <?= _("Stripe Publishable Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $stripe['publishable_key_value']; ?>" 
               class="form-control" 
               id="inputStripePublishableKey" 
               name="input[stripe_publishable_key]" 
               placeholder="<?= _("Enter Publishable Key"); ?>">
        <p class="help-block">
            <?= $stripe['publishable_key_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $stripe['security_key_error_class']; ?>">
        <label class="control-label" for="inputStripeSecurityKey">
            <?= _("Stripe Security Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $stripe['security_key_value']; ?>" 
               class="form-control" 
               id="inputStripeSecurityKey" 
               name="input[stripe_security_key]" 
               placeholder="<?= _("Enter Stripe Security Key"); ?>">
        <p class="help-block">
            <?= $stripe['security_key_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $stripe['signing_secret_error_class']; ?>">
        <label class="control-label" for="inputStripeSigningSecret">
            <?= _("Stripe Signing Secret"); ?>:
        </label>
        <input type="text" 
               value="<?= $stripe['signing_secret_value']; ?>" 
               class="form-control" 
               id="inputStripeSigningSecret" 
               name="input[stripe_signing_secret]" 
               placeholder="<?= _("Enter Stripe Signing Secret"); ?>">
        <p class="help-block">
            <?= $stripe['signing_secret_info']; ?>
        </p>
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[stripe_userid_vendorid_metadata]" 
                   value="1" 
                   <?= $stripe['userid_vendorid_metadata_checked']; ?>>
                <?= _("Collect userId and vendorId metadata"); ?>
        </label>
        <p class="help-block">
            <?= $stripe['userid_vendorid_metadata_description']; ?>
        </p>
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[stripe_test]" 
                   value="1" 
                   <?= $stripe['test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test account."); ?>
        </p>
    </div>
</div>