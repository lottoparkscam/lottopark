<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $piastrix_payment = new Forms_Whitelabel_Payment_Piastrix();
    $piastrix_payment->set_whitelabel($whitelabel);
    $piastrix = $piastrix_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsPiastrix" class="payment-details hidden">
    <h3><?= _("Piastrix integration details"); ?></h3>
    
    <div class="form-group <?= $piastrix['shopid_error_class']; ?>">
        <label class="control-label" for="inputShopID">
            <?= _("Shop ID"); ?>:
        </label>
        <input type="number" 
               value="<?= $piastrix['shop_id_value']; ?>" 
               class="form-control" 
               id="inputShopID" 
               name="input[shopid]" 
               placeholder="<?= _("Enter Shop ID"); ?>">
        <p class="help-block">
            <?= $piastrix['show_id_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $piastrix['secret_key_error_class']; ?>">
        <label class="control-label" for="inputSecretKey">
            <?= _("Secret Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $piastrix['secret_key_value']; ?>" 
               class="form-control" 
               id="inputSecretKey" 
               name="input[secretkey]" 
               placeholder="<?= _("Enter Secret Key"); ?>">
        <p class="help-block">
            <?= $piastrix['secret_key_info']; ?>
        </p>
        <p class="help-block">
            <?= $piastrix['help_text']; ?>
        </p>
    </div>
</div>