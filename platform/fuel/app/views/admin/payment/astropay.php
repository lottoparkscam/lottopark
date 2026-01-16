<div id="paymentDetailsAstroPay" class="payment-details hidden">
    <h3>
        <?= _("AstroPay integration details"); ?>
    </h3>
    <p class="help-block">
        <?= _('Remember: register your server IP in the panel') . ' ' ?>
        <a href="https://merchant.astropay.com/">https://merchant.astropay.com/</a>
        <?= ' ' . _('in section "Integration->Credentials & Settings"') ?>.
    </p>
    <div class="form-group<?= $input_has_error_ap('login') ?>">
        <label class="control-label" for="inputAstroPayLogin">
            <?= _("AstroPay login") ?>:
        </label>
        <input type="text" 
               value="<?= $input_last_value_ap('login') ?>"
               class="form-control"
               id="inputAstroPayLogin"
               name="input[astro_pay_login]"
               placeholder="<?= _("Enter AstroPay Login") ?>">
        <p class="help-block">
            <?= _('Your merchant ID in AstroPay platform (x_login)') ?>. 
            <?= _('Can be found at Integration->Credentials & Settings in your AstroPay Merchant Panel') ?>.
        </p>
    </div>
    
    <div class="form-group<?= $input_has_error_ap('password') ?>">
        <label class="control-label" for="inputAstroPayPassword">
            <?= _("AstroPay password") ?>:
        </label>
        <input type="text" 
               value="<?= $input_last_value_ap('password') ?>"
               class="form-control"
               id="inputAstroPayPassword"
               name="input[astro_pay_password]"
               placeholder="<?= _("Enter AstroPay Password") ?>">
        <p class="help-block">
            <?= _('Your merchant password in AstroPay platform (x_trans_key)') ?>. 
            <?= _('Can be found at Integration->Credentials & Settings in your AstroPay Merchant Panel') ?>.
        </p>
    </div>

    <div class="form-group<?= $input_has_error_ap('secret_key') ?>">
        <label class="control-label" for="inputAstroPaySecretKey">
            <?= _("AstroPay secret key") ?>:
        </label>
        <input type="text" 
               value="<?= $input_last_value_ap('secret_key') ?>"
               class="form-control"
               id="inputAstroPaySecretKey"
               name="input[astro_pay_secret_key]"
               placeholder="<?= _("Enter AstroPay SecretKey") ?>">
        <p class="help-block">
            <?= _('Your secret key in AstroPay platform (secret_key)') ?>. 
            <?= _('Can be found at Integration->Credentials & Settings in your AstroPay Merchant Panel') ?>.
        </p>
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[astro_pay_is_test]"
                   value="1" <?= $checked_ap('is_test') ?>>
            <?= _("Test account") ?>
        </label>
        <p class="help-block"><?= _("Check it for test account") ?>.
    </div>
</div>
