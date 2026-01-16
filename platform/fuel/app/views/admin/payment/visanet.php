<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $visanet_payment = new Forms_Whitelabel_Payment_VisaNet();
    $visanet = $visanet_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsVisaNet" class="payment-details hidden">
    <h3><?= _("VisaNet integration details"); ?></h3>

    <div class="form-group <?= $visanet['visanet_user_error_class']; ?>">
        <label class="control-label" for="inputVisaNetUser">
            <?= _("VisaNet user"); ?>:
        </label>
        <input type="text" 
               value="<?= $visanet['visanet_user_value']; ?>" 
               class="form-control" 
               id="inputVisaNetUser" 
               name="input[visanet_user]" 
               placeholder="<?= _("Enter VisaNet user"); ?>">
    </div>
    
    <div class="form-group <?= $visanet['visanet_password_error_class']; ?>">
        <label class="control-label" for="inputVisaNetPassword">
            <?= _("VisaNet password"); ?>:
        </label>
        <input type="text" 
               value="<?= $visanet['visanet_password_value']; ?>" 
               class="form-control" 
               id="inputVisaNetPassword" 
               name="input[visanet_password]" 
               placeholder="<?= _("Enter VisaNet password"); ?>">
    </div>
    
    <div class="form-group <?= $visanet['visanet_merchantid_error_class']; ?>">
        <label class="control-label" for="inputVisaNetMerchantID">
            <?= _("VisaNet merchantid"); ?>:
        </label>
        <input type="text" 
               value="<?= $visanet['visanet_merchantid_value']; ?>" 
               class="form-control" 
               id="inputVisaNetMerchantID" 
               name="input[visanet_merchantid]" 
               placeholder="<?= _("Enter merchantid"); ?>">
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[visanet_test]" 
                   value="1" <?= $visanet['visanet_test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test account."); ?>
        </p>
    </div>
    
</div>