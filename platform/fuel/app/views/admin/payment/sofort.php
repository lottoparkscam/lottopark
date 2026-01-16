<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $sofort_payment = new Forms_Whitelabel_Payment_Sofort();
    $sofort = $sofort_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsSofort" class="payment-details hidden">
    <h3>
        <?= _("Sofort integration details"); ?>
    </h3>
    
    <div class="form-group <?= $sofort['config_key_error_class']; ?>">
        <label class="control-label" for="inputConfigKey">
            <?= _("Configuration Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $sofort['config_key_value']; ?>" 
               class="form-control" 
               id="inputConfigKey" 
               name="input[configkey]" 
               placeholder="<?= _("Enter Configuration Key"); ?>">
        <p class="help-block">
            <?= $sofort['config_key_info']; ?>
        </p>
    </div>
</div>