<div id="paymentDetailsEasyPaymentGateway" class="payment-details hidden">
    <h3>
        <?= _("EasyPaymentGateway integration details"); ?>
    </h3>
    
    <div class="form-group<?= $input_has_error_epg('merchant_id') ?>">
        <label class="control-label" for="inputEasyPaymentGatewayMerchantId">
            <?= _("EasyPaymentGateway Merchant ID") ?>:
        </label>
        <input type="text" 
               value="<?= $input_last_value_epg('merchant_id') ?>"
               class="form-control"
               id="inputEasyPaymentGatewayMerchantId"
               name="input[easy_payment_gateway_merchant_id]"
               placeholder="<?= _("Enter EasyPaymentGateway Merchant ID") ?>">
        <p class="help-block">
            <?= _('Merchant id for your EPG account.') ?>
        </p>
    </div>
    
    <div class="form-group<?= $input_has_error_epg('product_id') ?>">
        <label class="control-label" for="inputEasyPaymentGatewayProductId">
            <?= _("EasyPaymentGateway Product ID") ?>:
        </label>
        <input type="text" 
               value="<?= $input_last_value_epg('product_id') ?>"
               class="form-control"
               id="inputEasyPaymentGatewayProductId"
               name="input[easy_payment_gateway_product_id]"
               placeholder="<?= _("Enter EasyPaymentGateway Product ID") ?>">
        <p class="help-block">
            <?= _('Product id for your EPG account.') ?>
        </p>
    </div>
    
    <div class="form-group<?= $input_has_error_epg('merchant_password') ?>">
        <label class="control-label" for="inputEasyPaymentGatewayMerchantPassword">
            <?= _("EasyPaymentGateway Merchant Password") ?>:
        </label>
        <input type="text" 
               value="<?= $input_last_value_epg('merchant_password') ?>"
               class="form-control"
               id="inputEasyPaymentGatewayMerchantPassword"
               name="input[easy_payment_gateway_merchant_password]"
               placeholder="<?= _("Enter EasyPaymentGateway Merchant Password") ?>">
        <p class="help-block">
            <?= _('Exactly 32 characters long md5 hash, produced from your EPG account merchant password.') ?>
        </p>
    </div>
    
    <div class="form-group<?= $input_has_error_epg('top_logo_url') ?>">
        <label class="control-label" for="inputEasyPaymentGatewayTopLogoUrl">
            <?= _("EasyPaymentGateway Top Logo Url") ?>:
        </label>
        <input type="text" 
               value="<?= $input_last_value_epg('top_logo_url') ?>"
               class="form-control"
               id="inputEasyPaymentGatewayTopLogoUrl"
               name="input[easy_payment_gateway_top_logo_url]"
               placeholder="<?= _("Enter EasyPaymentGateway Top Logo Url") ?>">
        <p class="help-block">
            <?= _('Address of the logo image, which will be shown on payment page.') ?>
        </p>
    </div>
    
    <div class="form-group<?= $input_has_error_epg('subtitle') ?>">
        <label class="control-label" for="inputEasyPaymentGatewaySubtitle">
            <?= _("EasyPaymentGateway Subtitle") ?>:
        </label>
        <input type="text" 
               value="<?= $input_last_value_epg('subtitle') ?>"
               class="form-control"
               id="inputEasyPaymentGatewaySubtitle"
               name="input[easy_payment_gateway_subtitle]"
               placeholder="<?= _("Enter EasyPaymentGateway Subtitle") ?>">
        <p class="help-block">
            <?= _('Subtitle, which will be shown on payment page.') ?>
        </p>
    </div>

    <div class="form-group<?= $input_has_error_epg('payment_solution') ?>">
        <label class="control-label" for="inputEasyPaymentGatewayPaymentSolution">
            <?= _("EasyPaymentGateway PaymentSolution") ?>:
        </label>
        <input  type="text"
                value="<?= $input_last_value_epg('payment_solution') ?>"
                class="form-control"
                id="inputEasyPaymentGatewayPaymentSolution"
                name="input[easy_payment_gateway_payment_solution]"
                placeholder="<?= _("Enter EasyPaymentGateway PaymentSolution") ?>">
        <p class="help-block">
            <?= _('Enter payment solution e.g. creditcards or astropaydirect or safetypay.') ?>
        </p>
    </div>
    
    <div class="checkbox">
        <label>
            <input type="checkbox"
                   name="input[easy_payment_gateway_is_test]"
                   value="1" <?= $checked_epg('is_test') ?>>
            <?= _("Test account") ?>
        </label>
        <p class="help-block"><?= _("Check it for test account.") ?>
    </div>
</div>
