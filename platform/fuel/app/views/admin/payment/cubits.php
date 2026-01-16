<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $cubits_payment = new Forms_Whitelabel_Payment_Cubits();
    $cubits = $cubits_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsCubits" class="payment-details hidden">
    <h3>
        <?= _("Cubits integration details"); ?>
    </h3>
    
    <div class="form-group <?= $cubits['apikey_error_class']; ?>">
        <label class="control-label" for="inputCubitsApiKey">
            <?= _("API Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $cubits['apikey_value']; ?>" 
               class="form-control" 
               id="inputCubitsApiKey" 
               name="input[cubits_apikey]" 
               placeholder="<?= _("Enter API Key"); ?>">
    </div>
    
    <div class="form-group <?= $cubits['apisecret_error_class']; ?>">
        <label class="control-label" for="inputApiSecret">
            <?= _("Enter API Secret"); ?>:
        </label>
        <input type="text" 
               value="<?= $cubits['apisecret_value']; ?>" 
               class="form-control" 
               id="inputApiSecret" 
               name="input[cubits_apisecret]" 
               placeholder="<?= _("API Secret"); ?>">
        <p class="help-block">
            <?= $cubits['apisecret_info']; ?>
        </p>
    </div>
</div>