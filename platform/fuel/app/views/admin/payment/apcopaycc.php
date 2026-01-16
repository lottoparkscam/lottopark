<?php
    $errors_to_process = null;
    if (!empty($errors)) {
        $errors_to_process = $errors;
    }
    $apcopaycc_payment = new Forms_Whitelabel_Payment_Apcopaycc();
    $apcopaycc = $apcopaycc_payment->prepare_data_to_show($data, $errors_to_process);
?>
<div id="paymentDetailsApcopayCC" class="payment-details hidden">
    <h3>
        <?= _("Apcopay CC integration details"); ?>
    </h3>

    <div class="form-group <?= $apcopaycc['profileid_error_class']; ?>">
        <label class="control-label" for="inputProfileID">
            <?= _("Profile ID"); ?>:
        </label>
        <input type="text"
               value="<?= $apcopaycc['profileid_value']; ?>"
               class="form-control"
               id="inputProfileID"
               name="input[apcopaycc_profileid]"
               placeholder="<?= _("Enter Profile ID"); ?>">
    </div>

    <div class="form-group <?= $apcopaycc['secretword_error_class']; ?>">
        <label class="control-label" for="inputSecretWord">
            <?= _("Secret Word (Hash)"); ?>:
        </label>
        <input type="text"
               value="<?= $apcopaycc['secretword_value']; ?>"
               class="form-control"
               id="inputSecretWord"
               name="input[apcopaycc_secretword]"
               placeholder="<?= _("Enter Secret Word"); ?>">
        <p class="help-block">
        </p>
    </div>

    <div class="form-group <?= $apcopaycc['merchantcode_error_class']; ?>">
        <label class="control-label" for="inputMerchantCode">
            <?= _("Merchant Code"); ?>:
        </label>
        <input type="text"
               value="<?= $apcopaycc['merchantcode_value']; ?>"
               class="form-control"
               id="inputMerchantCode"
               name="input[apcopaycc_merchantcode]"
               placeholder="<?= _("Enter Merchant Code"); ?>">
    </div>

    <div class="form-group <?= $apcopaycc['password_error_class']; ?>">
        <label class="control-label" for="inputPassword">
            <?= _("Password"); ?>:
        </label>
        <input type="text"
               value="<?= $apcopaycc['password_value']; ?>"
               class="form-control"
               id="inputPassword"
               name="input[apcopaycc_password]"
               placeholder="<?= _("Enter Password"); ?>">
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[apcopaycc_3dsecure]"
                   value="1" 
                   <?= $apcopaycc['checked_3dsecure']; ?>>
            <?= _("Secure 3D"); ?>
        </label>
        <p class="help-block">
            <?= _("Check it to enable 3D Secure."); ?>
        </p>
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[apcopaycc_bypass3ds]"
                   value="1" 
                   <?= $apcopaycc['checked_bypass3ds']; ?>>
            <?= _("Bypass 3DS"); ?>
        </label>
        <p class="help-block">
            <?= _("Bypass node will skip 3DSecure procedure"); ?>
        </p>
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[apcopaycc_only3ds]"
                   value="1" 
                   <?= $apcopaycc['checked_only3ds']; ?>>
            <?= _("Only 3DS"); ?>
        </label>
        <p class="help-block">
            <?= _("Abort transaction if cardholder is not eligible for 3DSecure"); ?>
        </p>
    </div>
</div>

