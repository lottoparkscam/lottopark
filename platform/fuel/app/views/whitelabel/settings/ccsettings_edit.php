<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");

$default_currency_tab = Helpers_Currency::get_mtab_currency();
$default_currency_code = $default_currency_tab['code'];
$default_currency_id = $default_currency_tab['id'];

$action_url = "/ccsettings/";
if (isset($edit_lp)) {
    $action_url .= 'edit/' . $edit_lp;
} else {
    $action_url .= 'new';
}
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Credit Card"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can add or edit Credit Card payment methods."); ?>
        </p>
		<a href="/ccsettings" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" autocomplete="off" action="<?= $action_url; ?>">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }

                        $method_error_class = '';
                        if (isset($errors['input.method'])) {
                            $method_error_class = ' has-error';
                        }
                    ?>
                    <div class="form-group <?= $method_error_class; ?>">
                        <label class="control-label" for="inputCCMethod">
                            <?= _("Gateway"); ?>:
                        </label>
                        <select autofocus
                                required
                                name="input[method]"
                                id="inputCCMethod"
                                class="form-control">
                            <option value="0">
                                <?= _("Choose gateway"); ?>
                            </option>
                            <?php
                                foreach ($methods as $key => $method):
                                    if (!isset($cmethods[$key]) || $cmethods[$key] == 0):
                                        $is_selected = '';
                                        if ((Input::post("input.method") !== null &&
                                                Input::post("input.method") == $key) ||
                                            (Input::post("input.method") === null &&
                                                isset($edit['method']) &&
                                                $edit['method'] == $key)
                                        ) {
                                            $is_selected = ' selected="selected"';
                                        }
                            ?>
                                        <option value="<?= $key; ?>"<?= $is_selected; ?>>
                                            <?= Security::htmlentities($method); ?>
                                        </option>
                            <?php
                                    endif;
                                endforeach;
                            ?>
                        </select>
                    </div>

                    <?php
                        $cost_p_error_class = '';
                        if (isset($errors['input.cost_percent'])) {
                            $cost_p_error_class = ' has-error';
                        }

                        $cost_p_value_temp = '0';
                        if (null !== Input::post("input.cost_percent")) {
                            $cost_p_value_temp = Input::post("input.cost_percent");
                        } elseif (isset($edit['cost_percent'])) {
                            $cost_p_value_temp = $edit['cost_percent'];
                        }
                        $cost_p_value = Security::htmlentities($cost_p_value_temp);
                    ?>
                    <div class="form-group <?= $cost_p_error_class; ?>">
                        <label class="control-label" for="inputCostPercentage">
                            <?= _("Percentage cost"); ?>:
                        </label>
                        <div class="input-group">
                            <input type="text"
                                   value="<?= $cost_p_value; ?>"
                                   class="form-control"
                                   id="inputCostPercentage"
                                   name="input[cost_percent]"
                                   placeholder="<?= _("Enter cost percentage"); ?>">
                            <div class="input-group-addon">%</div>
                        </div>
                        <p class="help-block">
                            <?= _("E.g. 4 if this payment method's company charge you 4%. Use dot for decimal digits. Not required."); ?>
                        </p>
                    </div>

                    <?php
                        $cost_f_error_class = '';
                        if (isset($errors['input.cost_fixed'])) {
                            $cost_f_error_class = ' has-error';
                        }

                        $cost_f_value_temp = '0';
                        if (null !== Input::post("input.cost_fixed")) {
                            $cost_f_value_temp = Input::post("input.cost_fixed");
                        } elseif (isset($edit['cost_fixed'])) {
                            $cost_f_value_temp = $edit['cost_fixed'];
                        }
                        $cost_f_value = Security::htmlentities($cost_f_value_temp);
                    ?>
                    <div class="form-group <?= $cost_f_error_class; ?>">
                        <label class="control-label" for="inputCostFixed">
                            <?= _("Fixed cost"); ?>:
                        </label>
                        <div class="row">
                            <div class="col-md-9">
                                <input type="text"
                                       required="required"
                                       value="<?= $cost_f_value; ?>"
                                       class="form-control"
                                       id="inputCostFixed"
                                       name="input[cost_fixed]"
                                       placeholder="<?= _("Enter cost percentage"); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="input[cost_currency]"
                                        id="inputCostCurrency"
                                        class="form-control">
                                    <?php
                                        foreach ($currencies as $currency_id => $currency):
                                            $is_selected = "";
                                            if ((Input::post("input.cost_currency") !== null &&
                                                    Input::post("input.cost_currency") == $currency_id) ||
                                                (Input::post("input.cost_currency") === null &&
                                                    isset($edit['cost_currency_id']) &&
                                                    $edit['cost_currency_id'] == $currency_id)
                                            ) {
                                                $is_selected = ' selected="selected"';
                                            }
                                    ?>
                                            <option value="<?= $currency_id; ?>"<?= $is_selected; ?>>
                                                <?= Security::htmlentities($currency); ?>
                                            </option>
                                    <?php
                                        endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <p class="help-block">
                            <?=
                            _(
                                "E.g. 4 EUR if this payment method's company charge you " .
                                htmlentities('€') .
                                "4. Use dot for decimal digits. Not required."
                            );
                            ?>
                        </p>
                    </div>

                    <?php
                        $payment_curr_error_class = '';
                        if (isset($errors['input.payment_currency'])) {
                            $payment_curr_error_class = ' has-error';
                        }
                    ?>
                    <div class="form-group hidden <?= $payment_curr_error_class; ?>">
                        <label>
                            <?= _("Payment currency"); ?>:
                        </label>
                        <div class="row">
                            <div class="col-md-3">
                                <select name="input[payment_currency]"
                                        id="inputPaymentCurrency"
                                        class="form-control">
                                    <?php 
                                        // TODO: Add multi currencies served by CC?
                                        foreach ($currencies as $currency_id => $currency):
                                            $is_selected = "";
                                            if ((Input::post("input.payment_currency") !== null &&
                                                    Input::post("input.payment_currency") == $currency_id) ||
                                                (Input::post("input.payment_currency") === null &&
                                                    isset($edit['payment_currency_id']) &&
                                                    $edit['payment_currency_id'] == $currency_id)
                                            ) {
                                                $is_selected = ' selected="selected"';
                                            }

                                            if (empty($is_selected) &&
                                                    !isset($edit['payment_currency_id']) &&
                                                    ($default_currency_id == $currency_id)
                                            ) {
                                                $is_selected = ' selected="selected"';
                                            }
                                    ?>
                                            <option value="<?= $currency_id; ?>"<?= $is_selected; ?>>
                                                <?= Security::htmlentities($currency); ?>
                                            </option>
                                    <?php
                                        endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="paymentDetailsEMerchantPay" class="payment-details hidden">
                        <h3>
                            <?= _("eMerchantPay Gateway Settings"); ?>
                        </h3>
                        <div class="form-group<?php if (isset($errors['input.accountid'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputAccountID">
                                <?= _("Account ID"); ?>:
                            </label>
                            <input type="text"
                                   value="<?= Security::htmlentities(null !== Input::post("input.accountid") ? Input::post("input.accountid") : (isset($data['accountid']) ? $data['accountid'] : '')); ?>"
                                   class="form-control"
                                   id="inputAccountID"
                                   name="input[accountid]"
                                   placeholder="<?= _("Enter Account ID"); ?>">
                            <div class="help-block">
                                <?= _("Given by eMerchantPay or visible in your eMerchantPay account: <strong>Account &gt; My Account &gt; Details tab &gt; Account Details panel &gt; Account ID</strong>."); ?>
                            </div>
                        </div>
                        <div class="form-group<?php if (isset($errors['input.apikey'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputApiKey">
                                <?= _("API Key"); ?>:
                            </label>
                            <input type="text"
                                   value="<?= Security::htmlentities(null !== Input::post("input.apikey") ? Input::post("input.apikey") : (isset($data['apikey']) ? $data['apikey'] : '')); ?>"
                                   class="form-control"
                                   id="inputApiKey"
                                   name="input[apikey]"
                                   placeholder="<?= _("Enter API Key"); ?>">
                            <div class="help-block">
                                <?= _("Given by eMerchantPay or set up in your eMerchantPay account: <strong>Account &gt; My Account &gt; eCommerce tab &gt; API panel &gt; API Key</strong>."); ?>
                            </div>
                        </div>
                        <div class="form-group<?php if (isset($errors['input.endpoint'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputEndpoint">
                                <?= _("Endpoint URL"); ?>:
                            </label>
                            <input type="url"
                                   value="<?= Security::htmlentities(null !== Input::post("input.endpoint") ? Input::post("input.endpoint") : (isset($data['endpoint']) ? $data['endpoint'] : '')); ?>"
                                   class="form-control"
                                   id="inputEndpoint"
                                   name="input[endpoint]"
                                   placeholder="<?= _("Enter endpoint URL"); ?>">
                            <div class="help-block">
                                <?= _("Probably <strong>https://my.emerchantpay.com</strong>. Please consult with eMerchantPay."); ?></div>
                        </div>
                        <div class="form-group<?php if (isset($errors['input.secretkey'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputSecretKey">
                                <?= _("Secret key"); ?>:
                            </label>
                            <input type="text"
                                   value="<?= Security::htmlentities(null !== Input::post("input.secretkey") ? Input::post("input.secretkey") : (isset($data['secretkey']) ? $data['secretkey'] : '')); ?>"
                                   class="form-control"
                                   id="inputSecretKey"
                                   name="input[secretkey]"
                                   placeholder="<?= _("Enter secret key"); ?>">
                            <div class="help-block">
                                <?= sprintf(_("Can be read or set up in your eMerchantPay account: <strong>Account &gt; My Account &gt; Payment Forms tab &gt; Payment Form panel &gt; Secret Key</strong>. Also, please ensure the field <strong>Account &gt; My Account &gt; eCommerce tab &gt; eCommerce panel &gt; Notification subpanel &gt; Merchant Notification URL</strong> is set to <strong>%s</strong> and the following server notifications (<strong>Account &gt; My Account &gt; Notifications tab &gt; Server Notifications panel</strong>) are enabled: <strong>Order Success, Order Pending, Order Declined, Order Failure</strong>."), 'https://'.$whitelabel['domain'].'/order/confirm/emerchantpay/'); ?>
                            </div>
                        </div>
                        <div class="form-group<?php if (isset($errors['input.minorder'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputSecretKey">
                                <?= _("Minimum order"); ?>:
                            </label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <?= Lotto_View::format_currency_code($default_currency_code); ?>
                                </div>
                                <input type="text"
                                       required="required"
                                       value="<?= Security::htmlentities(null !== Input::post("input.minorder") ? Input::post("input.minorder") : (isset($data['minorder']) ? number_format($data['minorder'], 2, ".", "") : '0.00')); ?>"
                                       class="form-control"
                                       id="inputMinOrder"
                                       name="input[minorder]"
                                       placeholder="<?= _("Enter minimum order"); ?>">
                            </div>
                            <div class="help-block"><?= _("Should be the same as value in your eMerchantPay account: <strong>Account &gt; My Account &gt; Thresholds tab &gt; Individual Transaction Limits panel &gt; Minimum Amount per TX</strong> and/or <strong> Account &gt; My Account &gt; Details tab &gt; Account Access panel &gt; [choose specific account] &gt; Thresholds tab &gt; Individual Transaction Limits panel &gt; Minimum Amount per TX</strong>."); ?></div>
                        </div>
                        <div class="form-group<?php if (isset($errors['input.descriptor'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputSecretKey">
                                <?= _("Descriptor"); ?>:
                            </label>
                            <input type="text"
                                   value="<?= Security::htmlentities(null !== Input::post("input.descriptor") ? Input::post("input.descriptor") : (isset($data['descriptor']) ? $data['descriptor'] : '')); ?>"
                                   class="form-control"
                                   id="inputSecretKey"
                                   name="input[descriptor]"
                                   placeholder="<?= _("Enter descriptor"); ?>">
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                       name="input[test]"
                                       value="1"<?php if ((null !== Input::post("input.test") && Input::post("input.test") == 1) || (isset($data['test']) && $data['test'] == 1)): echo ' checked="checked"'; endif; ?>>
                                           <?= _("Test account"); ?>
                            </label>
                            <p class="help-block">
                                <?= _("Check it for test account."); ?>
                            </p>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
				</form>
			</div>
        </div>
    </div>
</div>
