<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $truevocc_payment = new Forms_Whitelabel_Payment_TruevoCC();
    $truevocc = $truevocc_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsTruevocc" class="payment-details hidden">
    <h3><?= _("Truevo CC integration details"); ?></h3>

    <div class="form-group <?= $truevocc['authorization_bearer_error_class']; ?>">
        <label class="control-label" for="inputTruevoCCAuthorizationBearer">
            <?= _("Authorization Bearer"); ?>:
        </label>
        <input type="text" 
               value="<?= $truevocc['authorization_bearer_value']; ?>" 
               class="form-control" 
               id="inputTruevoCCAuthorizationBearer" 
               name="input[truevocc_authorization_bearer]" 
               placeholder="<?= _("Authorization Bearer"); ?>">
    </div>
    
    <div class="form-group <?= $truevocc['entity_id_error_class']; ?>">
        <label class="control-label" for="inputEntityID">
            <?= _("Entity ID"); ?>:
        </label>
        <input type="text" 
               value="<?= $truevocc['entity_id_value']; ?>" 
               class="form-control" 
               id="inputEntityID" 
               name="input[truevocc_entity_id]" 
               placeholder="<?= _("Enter Entity ID"); ?>">
    </div>
    
    <div class="form-group <?= $truevocc['brands_error_class']; ?>">
        <label class="control-label" for="inputBrands">
            <?= _("Brands"); ?>:
        </label>
        <input type="text" 
               value="<?= $truevocc['brands_value']; ?>" 
               class="form-control" 
               id="inputBrands" 
               name="input[truevocc_brands]" 
               placeholder="<?= _("Enter Brands"); ?>">
        <p class="help-block">
            <?= $truevocc['brands_info']; ?>
        </p>
    </div>
    
    <div class="form-group <?= $truevocc['descriptor_error_class']; ?>">
        <label class="control-label" for="inputDescriptor">
            <?= _("Descriptor"); ?>:
        </label>
        <input type="text" 
               value="<?= $truevocc['descriptor_value']; ?>" 
               class="form-control" 
               id="inputDescriptor" 
               name="input[truevocc_descriptor]" 
               placeholder="<?= _("Enter Descriptor"); ?>">
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox" 
                   name="input[truevocc_test]" 
                   value="1" <?= $truevocc['test_checked']; ?>>
                <?= _("Test account"); ?>
        </label>
        <p class="help-block">
            <?= $truevocc['test_info']; ?>
        </p>
    </div>
</div>