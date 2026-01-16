<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $entercash_payment = new Forms_Whitelabel_Payment_Entercash();
    $entercash = $entercash_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsEntercash" class="payment-details hidden">
    <h3>
        <?= _("Entercash integration details"); ?>
    </h3>
    
    <div class="form-group <?= $entercash['api_id_error_class']; ?>">
        <label class="control-label" for="inputAPIID">
            <?= _("API ID"); ?>:
        </label>
        <input type="text" 
               value="<?= $entercash['api_id_value']; ?>" 
               class="form-control" 
               id="inputAPIID" 
               name="input[apiid]" 
               placeholder="<?= _("Enter API ID"); ?>">
        <p class="help-block">
            <?= $entercash['api_id_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $entercash['private_key_error_class']; ?>">
        <label class="control-label" for="inputPrivateKey">
            <?= _("Private Key"); ?>:
        </label>
        <textarea style="width: 650px;" 
                  rows="15" 
                  class="form-control" 
                  id="inputPrivateKey" 
                  name="input[privatekey]" 
                  placeholder="<?= _("Enter Private Key"); ?>"><?= $entercash['private_key_value']; ?></textarea>
        <p class="help-block">
            <?= $entercash['private_key_info']; ?>
        </p>
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[entercash_test]" 
                   value="1" 
                   <?= $entercash['test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test account."); ?>
        </p>
    </div>
</div>