<div id="paymentDetailsGcash" class="payment-details hidden">
    <h3>
        <?= _("Gcash integration details"); ?>
    </h3>

    <div class="form-group<?= $input_has_error_gcash('merchant_id') ?>">
        <label class="control-label" for="inputGcashMerchantId">
            <?= _('Merchant ID') ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_gcash('merchant_id') ?>"
               class="form-control"
               id="inputGcashMerchantId"
               name="input[gcash_merchant_id]"
               placeholder="<?= _('Enter Merchant ID') ?>">
    </div>

    <div class="form-group<?= $input_has_error_gcash('merchant_name') ?>">
        <label class="control-label" for="inputGcashMerchantName">
            <?= _('Merchant Name') ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_gcash('merchant_name') ?>"
               class="form-control"
               id="inputGcashMerchantName"
               name="input[gcash_merchant_name]"
               placeholder="<?= _('Enter Merchant Name') ?>">
    </div>

    <div class="form-group<?= $input_has_error_gcash('api_client_id') ?>">
        <label class="control-label" for="inputGcashApiClientId">
            <?= _('API Client ID') ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_gcash('api_client_id') ?>"
               class="form-control"
               id="inputGcashApiClientId"
               name="input[gcash_api_client_id]"
               placeholder="<?= _('Enter API Client ID') ?>">
    </div>

    <div class="form-group<?= $input_has_error_gcash('api_key_secret') ?>">
        <label class="control-label" for="inputGcashApiKeySecret">
            <?= _('API Key Secret') ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_gcash('api_key_secret') ?>"
               class="form-control"
               id="inputGcashApiKeySecret"
               name="input[gcash_api_key_secret]"
               placeholder="<?= _('Enter API key secret') ?>">
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[gcash_is_test]"
                   value="1" <?= $checked_gcash('is_test') ?>>
            <?= _("Test account") ?>
        </label>
        <p class="help-block"><?= _("Check it for test account.") ?>
    </div>

</div>
