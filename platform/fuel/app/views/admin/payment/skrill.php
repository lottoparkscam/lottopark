<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $skrill_payment = new Forms_Whitelabel_Payment_Skrill();
    $skrill = $skrill_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsSkrill" class="payment-details hidden">
    <h3>
        <?= _("Skrill integration details"); ?>
    </h3>
    
    <div class="form-group <?= $skrill['merchantemail_error_class']; ?>">
        <label class="control-label" for="inputMerchantEmail">
            <?= _("Merchant E-mail (required)"); ?>:
        </label>
        <input type="email" 
               value="<?= $skrill['merchantemail_value']; ?>" 
               class="form-control" 
               id="inputMerchantEmail" 
               name="input[merchantemail]" 
               placeholder="<?= _("Enter merchant account e-mail"); ?>">
    </div>
    
    <div class="form-group <?= $skrill['secretword_error_class']; ?>">
        <label class="control-label" for="inputSecretWord">
            <?= _("Secret Word (required)"); ?>:
        </label>
        <input type="text" 
               value="<?= $skrill['secretword_value']; ?>" 
               class="form-control" 
               id="inputSecretWord" 
               name="input[secretword]" 
               placeholder="<?= _("Enter Skrill secret word"); ?>">
        <p class="help-block">
            <?= $skrill['secretword_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $skrill['merchantlogourl_error_class']; ?>">
        <label class="control-label" for="inputMerchantLogoURL">
            <?= _("Merchant Logo URL"); ?>:
        </label>
        <input type="text" 
               value="<?= $skrill['merchantlogourl_value']; ?>" 
               class="form-control" 
               id="inputMerchantLogoURL" 
               name="input[merchantlogourl]" 
               placeholder="<?= _("Enter merchant logo URL"); ?>">
        <p class="help-block">
            <?= _("At least 107px &times; 65px."); ?>
        </p>
    </div>
    
    <div class="form-group <?= $skrill['merchantdescription_error_class']; ?>">
        <label class="control-label" for="inputMerchantDescription">
            <?= _("Merchant Description"); ?>:
        </label>
        <input type="text" 
               value="<?= $skrill['merchantdescription_value']; ?>" 
               class="form-control" 
               id="inputMerchantDescription" 
               name="input[merchantdescription]" 
               placeholder="<?= _("Enter merchant description (logo alt)"); ?>">
    </div>
</div>
