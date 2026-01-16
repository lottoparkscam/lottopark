<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $creditcardsandbox_payment = new Forms_Whitelabel_Payment_CreditCardSandbox();
    $creditcardsandbox = $creditcardsandbox_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsCreditCardSandbox" class="payment-details hidden">
    <h3><?= _("Credit Card Sandbox integration details"); ?></h3>

    <div class="form-group <?= $creditcardsandbox['url_to_redirect_error_class']; ?>">
        <label class="control-label" for="inputCreditCardCandboxUrlToRedirect">
            <?= _("Credit Card Sandbox Url to redirect on"); ?>:
        </label>
        <input type="text" 
               value="<?= $creditcardsandbox['url_to_redirect_value']; ?>" 
               class="form-control" 
               id="inputCreditCardCandboxUrlToRedirect" 
               name="input[creditcardsandbox_url_to_redirect]" 
               placeholder="<?= _("Enter Url"); ?>">
        <p class="help-block">
            <?= $creditcardsandbox['url_to_redirect_info']; ?>
        </p>
    </div>
</div>