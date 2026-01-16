<div id="paymentDetailsPicksell" class="payment-details hidden">
    <h3>
        <?= _("Picksell integration details"); ?>
    </h3>

    <div class="form-group<?= $input_has_error_picksell('merchant_id') ?>">
        <label class="control-label" for="inputPicksellMerchantId">
            <?= _("Picksell Merchant ID") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_picksell('merchant_id') ?>"
               class="form-control"
               id="inputPicksellMerchantId"
               name="input[picksell_merchant_id]"
               placeholder="<?= _("Enter Picksell Merchant ID") ?>">
    </div>

    <div class="form-group<?= $input_has_error_picksell('api_key_token') ?>">
        <label class="control-label" for="inputPicksellApiKeyToken">
            <?= _("Picksell API key token") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_picksell('api_key_token') ?>"
               class="form-control"
               id="inputPicksellApiKeyToken"
               name="input[picksell_api_key_token]"
               placeholder="<?= _("Enter API key token") ?>">
    </div>

    <div class="form-group<?= $input_has_error_picksell('api_key_secret') ?>">
        <label class="control-label" for="inputPicksellApiKeySecret">
            <?= _("Picksell API key secret") ?>:
        </label>
        <input type="text"
               value="<?= $input_last_value_picksell('api_key_secret') ?>"
               class="form-control"
               id="inputPicksellApiKeySecret"
               name="input[picksell_api_key_secret]"
               placeholder="<?= _("Enter API key secret") ?>">
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[picksell_is_test]"
                   value="1" <?= $checked_picksell('is_test') ?>>
            <?= _("Test account") ?>
        </label>
        <p class="help-block"><?= _("Check it for test account.") ?>
    </div>
</div>
