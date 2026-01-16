<div id="paymentDetailsPspGate" class="payment-details hidden">
    <h3>
        <?= _("PSPGATE integration details"); ?>
    </h3>
    <p class="help-block">
        <?= _('Remember: default payment currency must be the same as terminal currency.') ?>
    </p>

    <p class="help-block">
        <?= "<b>Important! This gateway requires setting callback url for each terminal in merchant panel.</b></br >
            Go to Terminals -> Actions and set callback url to https://whitelotto.com/order/confirm/{whitelabel_payment_id}/</br >
            For type V2 whitelabel you can set https://{domain}/order/confirm/{whitelabel_payment_id}/" ?>
    </p>

    <div class="form-group<?= $input_has_error_pspgate('client_id') ?>">
        <label class="control-label" for="inputPspGateClientId">
            <?= _("PSPGATE Client ID") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_pspgate('client_id') ?>"
               class="form-control"
               id="inputPspGateClientId"
               name="input[pspgate_client_id]"
               placeholder="<?= _("Enter Client ID") ?>">
    </div>

    <div class="form-group<?= $input_has_error_pspgate('client_secret') ?>">
        <label class="control-label" for="inputPspGateClientSecret">
            <?= _("PSPGATE Client Secret") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_pspgate('client_secret') ?>"
               class="form-control"
               id="inputPspGateClientSecret"
               name="input[pspgate_client_secret]"
               placeholder="<?= _("Enter Client Secret") ?>">
    </div>

    <div class="form-group<?= $input_has_error_pspgate('username') ?>">
        <label class="control-label" for="inputPspGateUsername">
            <?= _("Username") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_pspgate('username') ?>"
               class="form-control"
               id="inputPspGateUsername"
               name="input[pspgate_username]"
               placeholder="<?= _("Enter terminal username") ?>">
    </div>

    <div class="form-group<?= $input_has_error_pspgate('password') ?>">
        <label class="control-label" for="inputPspGatePassword">
            <?= _("Password") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_pspgate('password') ?>"
               class="form-control"
               id="inputPspGatePassword"
               name="input[pspgate_password]"
               placeholder="<?= _("Enter terminal password") ?>">
    </div>

    <div class="form-group<?= $input_has_error_pspgate('api_password') ?>">
        <label class="control-label" for="inputPspGateApiPassword">
            <?= _("API Password") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_pspgate('api_password') ?>"
               class="form-control"
               id="inputPspGateApiPassword"
               name="input[pspgate_api_password]"
               placeholder="<?= _("Enter API password") ?>">
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[pspgate_is_test]"
                   value="1" <?= $checked_pspgate('is_test') ?>>
            <?= _("Test account") ?>
        </label>
        <p class="help-block"><?= _("Check it for test account.") ?>
    </div>
</div>
