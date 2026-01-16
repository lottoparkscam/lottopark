<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $entropay_payment = new Forms_Whitelabel_Payment_Entropay();
    $entropay = $entropay_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsEntropay" class="payment-details hidden">
    <h3>
        <?= _("Entropay integration details"); ?>
    </h3>
    
    <div class="form-group <?= $entropay['ref_error_class']; ?>">
        <label class="control-label" for="inputRef">
            <?= _("Referrer ID"); ?>:
        </label>
        <input type="text" 
               value="<?= $entropay['ref_value']; ?>" 
               class="form-control" 
               id="inputRef" 
               name="input[ref]" 
               placeholder="<?= _("Enter Referrer ID"); ?>">
        <p class="help-block">
            <?= _("Given by Entropay."); ?>
        </p>
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[entropaytest]" 
                   value="1" 
                   <?= $entropay['test_checked']; ?>>
                <?= _("Test environment"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it for test environment."); ?>
        </p>
    </div>
</div>