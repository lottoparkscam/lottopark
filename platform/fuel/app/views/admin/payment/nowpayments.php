<div id="paymentDetailsNowPayments" class="payment-details hidden">
    <h3>
        <?= _("NOWPayments integration details"); ?>
    </h3>

    <div class="form-group<?= $input_has_error_nowpayments('api_key') ?>">
        <label class="control-label" for="inputNowPaymentsApiKey">
            <?= _("API Key") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_nowpayments('api_key') ?>"
               class="form-control"
               id="inputNowPaymentsApiKey"
               name="input[nowpayments_api_key]"
               placeholder="<?= _("Enter API key") ?>">
    </div>

    <div class="form-group<?= $input_has_error_nowpayments('ipn_secret_key') ?>">
        <label class="control-label" for="inputNowPaymentsApiKeySecret">
            <?= _("IPN Secret Key") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_nowpayments('ipn_secret_key') ?>"
               class="form-control"
               id="inputNowPaymentsApiKeySecret"
               name="input[nowpayments_ipn_secret_key]"
               placeholder="<?= _("Enter IPN secret key") ?>">
        <p class="help-block">
            <?= _('Secret key is located in merchant panel and is used to verify integrity of webhooks.') ?>
        </p>
    </div>

    <div class="form-group<?= $input_has_error_nowpayments('force_payment_currency') ?>">
        <label class="control-label" for="inputNowPaymentsForcePaymentCurrency">
            <?= _("Force Payment Currency") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_nowpayments('force_payment_currency') ?>"
               class="form-control"
               id="inputNowPaymentsForcePaymentCurrency"
               name="input[nowpayments_force_payment_currency]"
               placeholder="<?= _("Enter cryptocurrency symbol") ?>">
        <p class="help-block">
            <?= _('Leave empty to allow user to select cryptocurrency on payment page (these can be set in merchant panel). Enter cryptocurrency symbol to force user to pay in specific crypto e.g. GGTKN. Sandbox might not have all cryptocurrencies.') ?>
        </p>
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[nowpayments_is_test]"
                   value="1" <?= $checked_nowpayments('is_test') ?>>
            <?= _("Test account") ?>
        </label>
        <p class="help-block"><?= _("Check it for test account.") ?>
    </div>
</div>
