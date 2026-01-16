<div id="paymentDetailsZen" class="payment-details hidden">
    <h3>
        ZEN integration details
    </h3>

    <div class="form-group<?= $input_has_error_zen('terminal_uuid') ?>">
        <label class="control-label" for="inputZenTerminalUuid">
            Terminal UUID:
        </label>
        <input type="text"
               value="<?= $input_last_value_zen('terminal_uuid') ?>"
               class="form-control"
               id="inputZenTerminalUuid"
               name="input[zen_terminal_uuid]"
               placeholder="Enter terminal UUID">
        <p class="help-block">
            In merchant panel open menu and go to Store settings in the Sell online section, then visit API & Documentation section.
        </p>
    </div>

    <div class="form-group<?= $input_has_error_zen('paywall_secret') ?>">
        <label class="control-label" for="inputZenPaywallSecret">
            Paywall secret:
        </label>
        <input type="text"
               value="<?= $input_last_value_zen('paywall_secret') ?>"
               class="form-control"
               id="inputZenPaywallSecret"
               name="input[zen_paywall_secret]"
               placeholder="Enter paywall secret">
    </div>

    <div class="form-group<?= $input_has_error_zen('merchant_ipn_secret') ?>">
        <label class="control-label" for="inputZenMerchantIpnSecret">
            Merchant IPN secret:
        </label>
        <input type="text"
               value="<?= $input_last_value_zen('merchant_ipn_secret') ?>"
               class="form-control"
               id="inputZenMerchantIpnSecret"
               name="input[zen_merchant_ipn_secret]"
               placeholder="Enter merchant IPN secret">
    </div>

    <!--- CASINO --->

    <div class="form-group<?= $input_has_error_zen('casino_terminal_uuid') ?>">
        <label class="control-label" for="inputZenTerminalUuid">
            Casino terminal UUID:
        </label>
        <input type="text"
               value="<?= $input_last_value_zen('casino_terminal_uuid') ?>"
               class="form-control"
               id="inputZenCasinoTerminalUuid"
               name="input[zen_casino_terminal_uuid]"
               placeholder="Enter casino terminal UUID">
        <p class="help-block">
            In merchant panel open menu and go to Store settings in the Sell online section, then visit API & Documentation section.
        </p>
    </div>

    <div class="form-group<?= $input_has_error_zen('casino_paywall_secret') ?>">
        <label class="control-label" for="inputZenCasinoPaywallSecret">
            Casino paywall secret:
        </label>
        <input type="text"
               value="<?= $input_last_value_zen('casino_paywall_secret') ?>"
               class="form-control"
               id="inputZenCasinoPaywallSecret"
               name="input[zen_casino_paywall_secret]"
               placeholder="Enter casino paywall secret">
    </div>

    <div class="form-group<?= $input_has_error_zen('casino_merchant_ipn_secret') ?>">
        <label class="control-label" for="inputZenCasinoMerchantIpnSecret">
            Casino merchant IPN secret:
        </label>
        <input type="text"
               value="<?= $input_last_value_zen('casino_merchant_ipn_secret') ?>"
               class="form-control"
               id="inputZenCasinoMerchantIpnSecret"
               name="input[zen_casino_merchant_ipn_secret]"
               placeholder="Enter casino merchant IPN secret">
    </div>



    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[zen_is_test]"
                   value="1" <?= $checked_zen('is_test') ?>>
            Test account
        </label>
        <p class="help-block">Check it for test account.</p>
    </div>
</div>
