<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $custom_payment = new Forms_Whitelabel_Payment_Custom();
    $custom = $custom_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsCustom" class="payment-details hidden">
    <h3><?= _("Custom integration details"); ?></h3>

    <div class="form-group <?= $custom['url_to_redirect_error_class']; ?>">
        <label class="control-label" for="inputCustomUrlToRedirect">
            <?= _("Custom Url to redirect on"); ?>:
        </label>
        <input type="text" 
               value="<?= $custom['url_to_redirect_value']; ?>" 
               class="form-control" 
               id="inputCustomUrlToRedirect" 
               name="input[custom_url_to_redirect]" 
               placeholder="<?= _("Enter Url"); ?>">
        <p class="help-block">
            <?= $custom['url_to_redirect_info']; ?>
        </p>
    </div>
</div>
