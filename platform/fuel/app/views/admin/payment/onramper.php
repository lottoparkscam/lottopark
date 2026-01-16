<div id="paymentDetailsOnramper" class="payment-details hidden">
    <h3>
        <?= _("Onramper integration details"); ?>
    </h3>

    <p class="help-block">
        <?= _('Note: there is no checkbox for test account. The API key determines between production/sandbox mode. Webhook URL is tied with API key and set by gateway team.') ?>
    </p>

    <div class="form-group<?= $input_has_error_onramper('api_key') ?>">
        <label class="control-label" for="inputOnramperApiKey">
            <?= _("API Key") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_onramper('api_key') ?>"
               class="form-control"
               id="inputOnramperApiKey"
               name="input[onramper_api_key]"
               placeholder="<?= _("Enter API key") ?>">
    </div>

    <div class="form-group<?= $input_has_error_onramper('api_key_secret') ?>">
        <label class="control-label" for="inputOnramperApiKeySecret">
            <?= _("API Key Secret") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_onramper('api_key_secret') ?>"
               class="form-control"
               id="inputOnramperApiKeySecret"
               name="input[onramper_api_key_secret]"
               placeholder="<?= _("Enter API key secret") ?>">
        <p class="help-block">
            <?= _('Secret key is provided by gateway team and is used to verify integrity of webhooks.') ?>
        </p>
    </div>
</div>
