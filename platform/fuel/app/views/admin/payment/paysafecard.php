<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $paysafecard_payment = new Forms_Whitelabel_Payment_Paysafecard();
    $paysafecard_payment->set_platform_ip($platform_ip);
    $paysafecard = $paysafecard_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsPaysafecard" class="payment-details hidden">
    <h3>
        <?= _("paysafecard integration details"); ?>
    </h3>
    
    <div class="form-group <?= $paysafecard['apikey_error_class']; ?>">
        <label class="control-label" for="inputPaysafecardApiKey">
            <?= _("API KEY"); ?>:
        </label>
        <input type="text" 
               value="<?= $paysafecard['apikey_value']; ?>" 
               class="form-control" 
               id="inputPaysafecardApiKey" 
               name="input[apikey]" 
               placeholder="<?= _("Enter API KEY"); ?>">
        <p class="help-block">
            <?= $paysafecard['help_text']; ?>
        </p>
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[paysafecardtest]" 
                   value="1" 
                   <?= $paysafecard['test_checked']; ?>>
                <?= _("Test environment"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test environment."); ?>
        </p>
    </div>
</div>