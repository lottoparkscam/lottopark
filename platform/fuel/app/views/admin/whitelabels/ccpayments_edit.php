<?php 
    include(APPPATH."views/admin/shared/navbar.php");
    
    $start_url = "/whitelabels/ccpayments/" . $whitelabel['id'];
    $action_url = $start_url . '/';
    if (isset($edit_lp)) {
        $action_url .= 'edit/' . $edit_lp;
    } else {
        $action_url .= 'new';
    }
    $urls = [
        'back' => $start_url,
        'action' => $action_url
    ];
    
    $default_currency_tab = Helpers_Currency::get_mtab_currency();
    
    $default_currency_id = $default_currency_tab['id'];
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php 
            include(APPPATH."views/admin/whitelabels/menu.php");
        ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Credit Card"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can add or edit Credit Card payment methods."); ?>
        </p>
		<a href="<?= $urls['back']; ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" autocomplete="off" action="<?= $urls['action']; ?>">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                        
                        $method_error_class = '';
                        if (isset($errors['input.method'])) {
                            $method_error_class = ' has-error';
                        }
                    ?>
                    <div class="form-group<?= $method_error_class; ?>">
                        <label class="control-label" for="inputCCMethod">
                            <?= _("Gateway"); ?>:
                        </label>
                        <select autofocus required 
                                name="input[method]" 
                                id="inputCCMethod" 
                                class="form-control">
                            <option value="0">
                                <?= _("Choose gateway"); ?>
                            </option>
                            <?php 
                                foreach ($methods as $key => $method):
                                    if (!isset($cmethods[$key]) || $cmethods[$key] == 0):
                                        $is_selected = "";
                                        if ((Input::post("input.method") !== null &&
                                                Input::post("input.method") == $key) ||
                                            (Input::post("input.method") === null &&
                                                isset($edit['method']) && $edit['method'] == $key)
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
                        $percentage_error_class = '';
                        if (isset($errors['input.cost_percent'])) {
                            $percentage_error_class = ' has-error';
                        }
                        $input_percentage_value = '0';
                        if (null !== Input::post("input.cost_percent")) {
                            $input_percentage_value = Input::post("input.cost_percent");
                        } elseif (isset($edit['cost_percent'])) {
                            $input_percentage_value = $edit['cost_percent'];
                        }
                    ?>
                    <div class="form-group<?= $percentage_error_class; ?>">
                        <label class="control-label" for="inputCostPercentage">
                            <?= _("Percentage cost"); ?>:
                        </label>
                        <div class="input-group">
                            <input type="text" 
                                   value="<?= Security::htmlentities($input_percentage_value); ?>" 
                                   class="form-control" 
                                   id="inputCostPercentage" 
                                   name="input[cost_percent]" 
                                   placeholder="<?= _("Enter cost percentage"); ?>">					  	
                            <div class="input-group-addon">%</div>
                        </div>
                        <p class="help-block">
                            <?php 
                                $help_text = _("E.g. 4 if this payment method's " .
                                    "company charge you 4%. Use dot for decimal " .
                                    "digits. Not required.");
                                echo $help_text;
                            ?>
                        </p>
                    </div>
                    
                    <?php 
                        $cost_error_class = '';
                        if (isset($errors['input.cost_fixed'])) {
                            $cost_error_class = ' has-error';
                        }
                        $input_cost_value = "0";
                        if (null !== Input::post("input.cost_fixed")) {
                            $input_cost_value = Input::post("input.cost_fixed");
                        } elseif (isset($edit['cost_fixed'])) {
                            $input_cost_value = $edit['cost_fixed'];
                        }
                    ?>
                    <div class="form-group<?= $cost_error_class; ?>">
                       <label class="control-label" for="inputCostFixed">
                           <?= _("Fixed cost"); ?>:
                       </label>
                       <div class="row">
                            <div class="col-md-9">
                                <input type="text" required="required" 
                                       value="<?= Security::htmlentities($input_cost_value); ?>" 
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
                            <?php 
                                $help_text = _("E.g. 4 EUR if this payment method's " .
                                    "company charge you 4 EUR. Use dot for decimal " .
                                    "digits. Not required.");
                                echo  $help_text;
                            ?>
                        </p>
                    </div>
                    
                    <?php
                        $currency_error_class = '';
                        if (isset($errors['input.payment_currency'])) {
                            $currency_error_class = ' has-error';
                        }
                    ?>
                    <div class="form-group <?= $currency_error_class; ?>">
                        <label>
                            <?= _("Payment currency"); ?>:
                        </label>
                        <div class="row">
                            <div class="col-md-3">
                                <select name="input[payment_currency]" 
                                        id="inputPaymentCurrency" 
                                        class="form-control">
                                    <?php 
                                        // TODO: add multi currencies served by CC?
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
                        
                        <?php
                            $accountid_error_class = '';
                            if (isset($errors['input.accountid'])) {
                                $accountid_error_class = ' has-error';
                            }
                            $accountid_value = '';
                            if (null !== Input::post("input.accountid")) {
                                $accountid_value = Input::post("input.accountid");
                            } elseif (isset($data['accountid'])) {
                                $accountid_value = $data['accountid'];
                            }
                        ?>
                        <div class="form-group<?= $accountid_error_class; ?>">
                            <label class="control-label" for="inputAccountID">
                                <?= _("Account ID"); ?>:
                            </label>
                            <input type="text" 
                                   value="<?= Security::htmlentities($accountid_value); ?>" 
                                   class="form-control" 
                                   id="inputAccountID" 
                                   name="input[accountid]" 
                                   placeholder="<?= _("Enter Account ID"); ?>">
                            <div class="help-block">
                                <?php 
                                    $help_text = _("Given by eMerchantPay or visible " .
                                        "in your eMerchantPay account: <strong>Account &gt; " .
                                        "My Account &gt; Details tab &gt; Account " .
                                        "Details panel &gt; Account ID</strong>.");
                                    echo  $help_text;
                                ?>
                            </div>
                        </div>
                        
                        <?php
                            $apikey_error_class = '';
                            if (isset($errors['input.apikey'])) {
                                $apikey_error_class = ' has-error';
                            }
                            $apikey_value = '';
                            if (null !== Input::post("input.apikey")) {
                                $apikey_value = Input::post("input.apikey");
                            } elseif (isset($data['apikey'])) {
                                $apikey_value = $data['apikey'];
                            }
                        ?>
                        <div class="form-group<?= $apikey_error_class; ?>">
                            <label class="control-label" for="inputApiKey">
                                <?= _("API Key"); ?>:
                            </label>
                            <input type="text" 
                                   value="<?= Security::htmlentities($apikey_value); ?>" 
                                   class="form-control" 
                                   id="inputApiKey" 
                                   name="input[apikey]" 
                                   placeholder="<?= _("Enter API Key"); ?>">
                            <div class="help-block">
                                <?php 
                                    $help_text = _("Given by eMerchantPay or set up " .
                                        "in your eMerchantPay account: <strong>Account &gt; " .
                                        "My Account &gt; eCommerce tab &gt; API panel &gt; " .
                                        "API Key</strong>.");
                                    echo $help_text;
                                ?>
                            </div>
                        </div>
                        
                        <?php
                            $endpoint_error_class = '';
                            if (isset($errors['input.endpoint'])) {
                                $endpoint_error_class = ' has-error';
                            }
                            $endpoint_value = '';
                            if (null !== Input::post("input.endpoint")) {
                                $endpoint_value = Input::post("input.endpoint");
                            } elseif (isset($data['endpoint'])) {
                                $endpoint_value = $data['endpoint'];
                            }
                        ?>
                        <div class="form-group<?= $endpoint_error_class; ?>">
                            <label class="control-label" for="inputEndpoint">
                                <?= _("Endpoint URL"); ?>:
                            </label>
                            <input type="url" 
                                   value="<?= Security::htmlentities($endpoint_value); ?>" 
                                   class="form-control" 
                                   id="inputEndpoint" 
                                   name="input[endpoint]" 
                                   placeholder="<?= _("Enter endpoint URL"); ?>">
                            <div class="help-block">
                                <?php 
                                    $help_text = _("Probably <strong>https://my.emerchantpay.com</strong>. " .
                                        "Please consult with eMerchantPay.");
                                    echo $help_text;
                                ?>
                            </div>
                        </div>
                        
                        <?php
                            $secretkey_error_class = '';
                            if (isset($errors['input.secretkey'])) {
                                $secretkey_error_class = ' has-error';
                            }
                            $secretkey_value = '';
                            if (null !== Input::post("input.secretkey")) {
                                $secretkey_value = Input::post("input.secretkey");
                            } elseif (isset($data['secretkey'])) {
                                $secretkey_value = $data['secretkey'];
                            }
                        ?>
                        <div class="form-group<?= $secretkey_error_class; ?>">
                            <label class="control-label" for="inputSecretKey">
                                <?= _("Secret key"); ?>:
                            </label>
                            <input type="text" 
                                   value="<?= Security::htmlentities($secretkey_value); ?>" 
                                   class="form-control" 
                                   id="inputSecretKey" 
                                   name="input[secretkey]" 
                                   placeholder="<?= _("Enter secret key"); ?>">
                            <div class="help-block">
                                <?php 
                                    $help_text = _("Can be read or set up in your " .
                                        "eMerchantPay account: <strong>Account &gt; " .
                                        "My Account &gt; Payment Forms tab &gt; Payment " .
                                        "Form panel &gt; Secret Key</strong>. " .
                                        "Also, please ensure the field <strong>Account &gt; " .
                                        "My Account &gt; eCommerce tab &gt; eCommerce " .
                                        "panel &gt; Notification subpanel &gt; Merchant " .
                                        "Notification URL</strong> is set to <strong>%s</strong> " .
                                        "and the following server notifications (<strong>Account &gt; " .
                                        "My Account &gt; Notifications tab &gt; Server " .
                                        "Notifications panel</strong>) are enabled: " .
                                        "<strong>Order Success, Order Pending, Order " .
                                        "Declined, Order Failure</strong>.");
                                    $link_text = 'https://' .
                                        $whitelabel['domain'] .
                                        '/order/confirm/emerchantpay/';
                                    $help_text_s = sprintf($help_text, $link_text);
                                    echo $help_text_s;
                                ?>
                            </div>
                        </div>
                        
                        <?php
                            $minorder_error_class = '';
                            if (isset($errors['input.minorder'])) {
                                $minorder_error_class = ' has-error';
                            }
                            $minorder_value = '0.00';
                            if (null !== Input::post("input.minorder")) {
                                $minorder_value = Input::post("input.minorder");
                            } elseif (isset($data['minorder'])) {
                                $minorder_value = number_format($data['minorder'], 2, ".", "");
                            }
                            $default_currency_code = Helpers_Currency::get_default_currency_code();
                            $default_cc_formatted = Lotto_View::format_currency_code($default_currency_code);
                        ?>
                        <div class="form-group<?= $minorder_error_class; ?>">
                            <label class="control-label" for="inputMinOrder">
                                <?= _("Minimum order"); ?>:
                            </label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <?= $default_cc_formatted; ?>
                                </div>
                                <input type="text" required="required" 
                                       value="<?= Security::htmlentities($minorder_value); ?>" 
                                       class="form-control" 
                                       id="inputMinOrder" 
                                       name="input[minorder]" 
                                       placeholder="<?= _("Enter minimum order"); ?>">
                            </div>
                            <div class="help-block">
                                <?php 
                                    $help_text = _("Should be the same as value in your " .
                                        "eMerchantPay account: <strong>Account &gt; " .
                                        "My Account &gt; Thresholds tab &gt; Individual " .
                                        "Transaction Limits panel &gt; Minimum Amount " .
                                        "per TX</strong> and/or <strong> Account &gt; " .
                                        "My Account &gt; Details tab &gt; Account Access " .
                                        "panel &gt; [choose specific account] &gt; " .
                                        "Thresholds tab &gt; Individual Transaction Limits " .
                                        "panel &gt; Minimum Amount per TX</strong>.");
                                    echo $help_text;
                                ?>
                            </div>
                        </div>
                        
                        <?php
                            $descriptor_error_class = '';
                            if (isset($errors['input.descriptor'])) {
                                $descriptor_error_class = ' has-error';
                            }
                            $descriptor_value = '';
                            if (null !== Input::post("input.descriptor")) {
                                $descriptor_value = Input::post("input.descriptor");
                            } elseif (isset($data['descriptor'])) {
                                $descriptor_value = $data['descriptor'];
                            }
                        ?>
                        <div class="form-group<?= $descriptor_error_class; ?>">
                            <label class="control-label" for="inputDescriptor">
                                <?= _("Descriptor"); ?>:
                            </label>
                            <input type="text" 
                                   value="<?= Security::htmlentities($descriptor_value); ?>" 
                                   class="form-control" 
                                   id="inputDescriptor" 
                                   name="input[descriptor]" 
                                   placeholder="<?= _("Enter descriptor"); ?>">
                        </div>
                        
                        <?php
                            $test_is_checked = '';
                            if ((null !== Input::post("input.test") &&
                                    Input::post("input.test") == 1) ||
                                (isset($data['test']) && $data['test'] == 1)
                            ) {
                                $test_is_checked = ' checked="checked"';
                            }
                        ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" 
                                       name="input[test]" 
                                       value="1"<?= $test_is_checked; ?>>
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

</div>
