<?php 
include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/admin/whitelabels/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= $title_text; ?>
        </h2>
        <p class="help-block">
            <?= $main_help_block_text; ?>
        </p>
        
        <a href="<?= $urls["currency"]; ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" autocomplete="off" action="<?= $urls["form_url"]; ?>">
                    <?php 
                        if (!empty($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                    ?>
                    <div class="row <?= $list_of_currencies_hide_class; ?>">
                        <div class="col-md-4 form-group">
                            <label class="control-label" >
                                <?= _("Currency"); ?>:
                            </label>
                            <select name="input[site_currency]" 
                                    id="inputSiteCurrency" 
                                    class="form-control" autofocus="true">
                                <?php 
                                    foreach ($currencies as $currency_entry):
                                ?>
                                        <option value="<?= $currency_entry["id"]; ?>" 
                                                data-code="<?= $currency_entry["code"]; ?>" 
                                                data-rate="<?= $currency_entry["rate"]; ?>" 
                                                <?= $currency_entry["is_selected"]; ?> 
                                                data-convertedmultiplier="<?= $currency_entry["converted_multiplier"]; ?>" 
                                                data-roundedingateway="<?= $currency_entry["rounded_in_gateway_currency"]; ?>"
                                                >
                                            <?= $currency_entry['code']; ?>
                                        </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <input type="hidden" 
                           value="<?= $default_system_currency_id; ?>" 
                           name="defaultsystemcurrencyid" 
                           id="defaultsystemcurrencyid">
                    <input type="hidden" 
                           value="<?= $gateway_currency_rate; ?>" 
                           name="gatewaycurrencyrate" 
                           id="gatewaycurrencyrate">
                    <input type="hidden" 
                           value="<?= $first_from_currencies['rate']; ?>" 
                           name="currencyrate" 
                           id="currencyrate">
                    <input type="hidden" 
                           value="<?= $first_from_currencies['converted_multiplier']; ?>" 
                           name="convertedmultiplier" 
                           id="convertedmultiplier">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <label>
                                <?= _("Default deposit values for user"); ?>:
                            </label>
                        </div>

                        <div class="col-md-4 form-group">
                            <span>
                                <?= _("in first box"); ?>:
                            </span>
                            <div class="input-group <?= $error_fields['first_box_deposit']; ?>">
                                <div class="input-group-addon deposit-currency">
                                    <?= $first_box["code"]; ?>
                                </div>
                                <input type="text" 
                                       value="<?= $first_box["value"]; ?>" 
                                       class="form-control special-box" 
                                       id="inputFirstBoxDeposit" 
                                       name="input[first_box_deposit]" 
                                       data-defaultmultiingateway="<?= $first_box["default_multi_in_gateway"]; ?>" 
                                       data-spanboxid="firstBoxDepositInEuro" 
                                       data-oldvalue="<?= $first_box["old_value"]; ?>" 
                                       data-locale="<?= $locale_text; ?>" 
                                       data-nantext="<?= $nantext; ?>" 
                                       data-greatermindepo="<?= $greatermindepo; ?>">
                            </div>
                            <div class="text-info">
                                <?= $first_box["default_currency_text"]; ?>
                                <span id="firstBoxDepositInEuro">
                                    <?= $first_box["in_gateway_currency"]; ?>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-4 form-group">
                            <span>
                                <?= _("in second box"); ?>:
                            </span>
                            <div class="input-group <?= $error_fields['second_box_deposit']; ?>">
                                <div class="input-group-addon deposit-currency">
                                    <?= $second_box["code"]; ?>
                                </div>
                                <input type="text" 
                                       value="<?= $second_box["value"]; ?>" 
                                       class="form-control special-box" 
                                       id="inputSecondBoxDeposit" 
                                       name="input[second_box_deposit]" 
                                       data-defaultmultiingateway="<?= $second_box["default_multi_in_gateway"]; ?>" 
                                       data-spanboxid="secondBoxDepositInEuro" 
                                       data-oldvalue="<?= $second_box["old_value"]; ?>" 
                                       data-locale="<?= $locale_text; ?>" 
                                       data-nantext="<?= $nantext; ?>" 
                                       data-greatermindepo="<?= $greatermindepo; ?>">
                            </div>
                            <div class="text-info">
                                <?= $second_box["default_currency_text"]; ?>
                                <span id="secondBoxDepositInEuro">
                                    <?= $second_box["in_gateway_currency"]; ?>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-4 form-group">
                            <span>
                                <?= _("in third box"); ?>:
                            </span>
                            <div class="input-group <?= $error_fields['third_box_deposit']; ?>">
                                <div class="input-group-addon deposit-currency">
                                    <?= $third_box["code"]; ?>
                                </div>
                                <input type="text" 
                                       value="<?= $third_box["value"]; ?>" 
                                       class="form-control special-box" 
                                       id="inputThirdBoxDeposit" 
                                       name="input[third_box_deposit]" 
                                       data-defaultmultiingateway="<?= $third_box["default_multi_in_gateway"]; ?>" 
                                       data-spanboxid="thirdBoxDepositInEuro" 
                                       data-oldvalue="<?= $third_box["old_value"]; ?>" 
                                       data-locale="<?= $locale_text; ?>" 
                                       data-nantext="<?= $nantext; ?>" 
                                       data-greatermindepo="<?= $greatermindepo; ?>">
                            </div>
                            <div class="text-info">
                                <?= $third_box["default_currency_text"]; ?>
                                <span id="thirdBoxDepositInEuro">
                                    <?= $third_box["in_gateway_currency"]; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="help-block">
                        <?= $help_block_text; ?>
                    </p>

                    <?php
                        if ($full_form):
                    ?>
                            <hr>
                            <div class="form-group <?= $error_fields['min_purchase_amount']; ?>">
                                <label class="control-label">
                                    <?= _("Minimum Payment by Currency"); ?>:
                                </label>
                                <div class="input-group">
                                    <div class="input-group-addon deposit-currency">
                                        <?= $min_purchase_box["code"]; ?>
                                    </div>
                                    <input type="text" 
                                           required="required" 
                                           value="<?= $min_purchase_box["value"]; ?>" 
                                           class="form-control special-box" 
                                           id="inputMinPurchaseAmount" 
                                           name="input[min_purchase_amount]" 
                                           data-defaultmultiingateway="<?= $min_purchase_box["default_multi_in_gateway"]; ?>" 
                                           data-oldvalue="<?= $min_purchase_box["old_value"]; ?>" 
                                           data-spanboxid="inputMinPurchaseAmountInEuro" 
                                           data-nantext="<?= $nantext; ?>" 
                                           placeholder="<?= _("Enter minimum payment amount"); ?>">
                                </div>
                                <div class="text-info">
                                    <?= $min_purchase_box["default_currency_text"]; ?>
                                    <span id="inputMinPurchaseAmountInEuro">
                                        <?= $min_purchase_box["in_gateway_currency"]; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group <?= $error_fields['min_deposit_amount']; ?>">
                                <label class="control-label">
                                    <?= _("Minimum Deposit by Currency"); ?>:
                                </label>
                                <div class="input-group">
                                    <div class="input-group-addon deposit-currency">
                                        <?= $min_deposit_box["code"]; ?>
                                    </div>
                                    <input type="text" 
                                           required="required" 
                                           value="<?= $min_deposit_box["value"]; ?>" 
                                           class="form-control special-box" 
                                           id="inputMindepositAmount" 
                                           name="input[min_deposit_amount]" 
                                           data-defaultmultiingateway="<?= $min_deposit_box["default_multi_in_gateway"]; ?>" 
                                           data-oldvalue="<?= $min_deposit_box["old_value"]; ?>" 
                                           data-spanboxid="inputMindepositAmountInEuro" 
                                           data-nantext="<?= $nantext; ?>" 
                                           placeholder="<?= _("Enter minimum deposit amount"); ?>">
                                </div>
                                <div class="text-info">
                                    <?= $min_deposit_box["default_currency_text"]; ?>
                                    <span id="inputMindepositAmountInEuro">
                                        <?= $min_deposit_box["in_gateway_currency"]; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group <?= $error_fields['min_withdrawal']; ?>">
                                <label class="control-label">
                                    <?= _("Minimum Withdrawal by Currency"); ?>:
                                </label>
                                <div class="input-group">
                                    <div class="input-group-addon deposit-currency">
                                        <?= $min_withdrawal_box["code"]; ?>
                                    </div>
                                    <input type="text" 
                                           required="required" 
                                           value="<?= $min_withdrawal_box["value"]; ?>" 
                                           class="form-control special-box" 
                                           id="inputMinWithdrawal" 
                                           name="input[min_withdrawal]" 
                                           data-defaultmultiingateway="<?= $min_withdrawal_box["default_multi_in_gateway"]; ?>" 
                                           data-oldvalue="<?= $min_withdrawal_box["old_value"]; ?>" 
                                           data-spanboxid="inputMinWithdrawalInEuro" 
                                           data-nantext="<?= $nantext; ?>" 
                                           placeholder="<?= _("Enter minimum withdrawal amount"); ?>">
                                </div>
                                <div class="text-info">
                                    <?= $min_withdrawal_box["default_currency_text"]; ?>
                                    <span id="inputMinWithdrawalInEuro">
                                        <?= $min_withdrawal_box["in_gateway_currency"]; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group <?= $error_fields['max_order_amount']; ?>">
                                <label class="control-label">
                                    <?= _("Maximum Order Amount by Currency"); ?>:
                                </label>
                                <div class="input-group">
                                    <div class="input-group-addon deposit-currency">
                                        <?= $max_order_amount_box["code"]; ?>
                                    </div>
                                    <input type="text" 
                                           required="required" 
                                           value="<?= $max_order_amount_box["value"]; ?>" 
                                           class="form-control special-box" 
                                           id="inputMaxOrderAmount" 
                                           name="input[max_order_amount]" 
                                           data-defaultmultiingateway="<?= $max_order_amount_box["default_multi_in_gateway"]; ?>" 
                                           data-oldvalue="<?= $max_order_amount_box["old_value"]; ?>" 
                                           data-spanboxid="inputMaxOrderAmountInEuro" 
                                           data-nantext="<?= $nantext; ?>" 
                                           placeholder="<?= _("Enter maximum order amount"); ?>">
                                </div>
                                <div class="text-info">
                                    <?= $max_order_amount_box["default_currency_text"]; ?>
                                    <span id="inputMaxOrderAmountInEuro">
                                        <?= $max_order_amount_box["in_gateway_currency"]; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="form-group <?= $error_fields['max_deposit_amount']; ?>">
                                <label class="control-label">
                                    <?= _("Maximum Deposit Amount by Currency"); ?>:
                                </label>
                                <div class="input-group">
                                    <div class="input-group-addon deposit-currency">
                                        <?= $max_deposit_amount_box["code"]; ?>
                                    </div>
                                    <input type="text" 
                                           required="required" 
                                           value="<?= $max_deposit_amount_box["value"]; ?>" 
                                           class="form-control special-box" 
                                           id="inputMaxDepositAmount" 
                                           name="input[max_deposit_amount]" 
                                           data-defaultmultiingateway="<?= $max_deposit_amount_box["default_multi_in_gateway"]; ?>" 
                                           data-oldvalue="<?= $max_deposit_amount_box["old_value"]; ?>" 
                                           data-spanboxid="inputMaxDepositAmountInEuro" 
                                           data-nantext="<?= $nantext; ?>" 
                                           placeholder="<?= _("Enter maximum deposit amount"); ?>">
                                </div>
                                <div class="text-info">
                                    <?= $max_deposit_amount_box["default_currency_text"]; ?>
                                    <span id="inputMaxDepositAmountInEuro">
                                        <?= $max_deposit_amount_box["in_gateway_currency"]; ?>
                                    </span>
                                </div>
                            </div>
                    <?php
                        endif;
                        
                        if ($show_default_tickbox):
                    ?>
                            <div class="form-group <?= $error_fields['is_default_for_site']; ?>">
                                <div class="input-group">
                                    <input type="checkbox" 
                                           name="input[is_default_for_site]" 
                                           value="1" 
                                            <?= $is_default_checked; ?>>
                                        <?= _("Make that currency default for site"); ?>
                                </div>
                            </div> 
                    <?php
                        endif;
                    ?>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php 
                            echo $button_submit_text;
                        ?>
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="infoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content text-danger">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> <?= _("Error"); ?>
                </h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _("OK"); ?></button>
            </div>
        </div>
    </div>
</div>