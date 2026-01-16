<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $astropaycard_payment = new Forms_Whitelabel_Payment_Astropaycard();
    $astropaycard = $astropaycard_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsAstroPayCard" class="payment-details hidden">
    <h3>
        <?= _("AstroPay Card integration details"); ?>
    </h3>
    
    <div class="form-group <?= $astropaycard['x_login_error_class']; ?>">
        <label class="control-label" for="inputAstroPayCardXLogin">
            <?= _("AstroPay Card x_login"); ?>:
        </label>
        <input type="text" 
               value="<?= $astropaycard['x_login_value']; ?>" 
               class="form-control" 
               id="inputAstroPayCardXLogin" 
               name="input[astropaycard_x_login]" 
               placeholder="<?= _("Enter AstroPay Card x_login"); ?>">
    </div>
    
    <div class="form-group <?= $astropaycard['x_trans_key_error_class']; ?>">
        <label class="control-label" for="inputAstroPayCardXTransKey">
            <?= _("AstroPay Card x_trans_key"); ?>:
        </label>
        <input type="text" 
               value="<?= $astropaycard['x_trans_key_value']; ?>" 
               class="form-control" 
               id="inputAstroPayCardXTransKey" 
               name="input[astropaycard_x_trans_key]" 
               placeholder="<?= _("Enter AstroPay Card x_trans_key"); ?>">
    </div>
    
    <div class="form-group <?= $astropaycard['secret_key_error_class']; ?>">
        <label class="control-label" for="inputAstroPayCardSecretKey">
            <?= _("AstroPay Card Secret Key"); ?>:
        </label>
        <input type="text" 
               value="<?= $astropaycard['secret_key_value']; ?>" 
               class="form-control" 
               id="inputAstroPayCardSecretKey" 
               name="input[astropaycard_secret_key]" 
               placeholder="<?= _("Enter AstroPay Card Secret Key"); ?>">
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[astropaycard_test]" 
                   value="1" 
                   <?= $astropaycard['test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test account."); ?>
        </p>
    </div>
</div>