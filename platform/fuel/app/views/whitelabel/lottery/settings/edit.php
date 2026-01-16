<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");

// Get default currency tab - for EUR currency
$default_currency_tab = Helpers_Currency::get_mtab_currency();
$second_quick_pick_lines_value = $quick_pick_lines_value * 2;
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Lottery settings"); ?> <small><?= _($lottery['name']); ?></small>
        </h2>
		<p class="help-block">
            <?= _("You can change lottery settings here."); ?>
        </p>
		<a href="/lotterysettings" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
			<?php
                include(APPPATH . "views/whitelabel/shared/messages.php");
            ?>
			<form method="post" action="/lotterysettings/edit/<?= $edit_lp; ?>">
                <div class="col-md-6">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                    ?>

                    <h3 class="nmt"><?= _("Lottery price"); ?></h3>

                    <div class="form-group <?= $model_error_class; ?>">
                        <label class="control-label" for="inputModel">
                            <?= _("Model"); ?>:
                        </label>
                        <select required 
                                name="input[model]" 
                                id="inputModel" 
                                class="form-control recalculate-price">
                            <?php
                                foreach ($model_names as $key => $model_name):
                                    $is_selected = '';
                                    if ((!empty(Input::post("model")) &&
                                            Input::post("model") == $key) ||
                                        (empty(Input::post("model")) &&
                                            $lottery['model'] == $key)
                                    ) {
                                        $is_selected = ' selected="selected"';
                                    }
                            ?>
                                    <option value="<?= $key; ?>" <?= $is_selected; ?>>
                                        <?= $model_name; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 form-group <?= $income_error_class; ?>">
                            <label class="control-label" for="inputIncome">
                                <?= _("Expected income"); ?>:
                            </label>
                            <input type="text" 
                                   required="required" 
                                   value="<?= $income_value; ?>" 
                                   class="form-control recalculate-price" 
                                   id="inputIncome" 
                                   name="input[income]" 
                                   placeholder="<?= _("Enter expected income"); ?>">
                        </div>
                        
                        <div class="col-md-4 form-group">
                            <label class="control-label" for="inputIncomeType">
                                <?= _("Type"); ?>:
                            </label>
                            <select required 
                                    name="input[income_type]" 
                                    id="inputIncomeType" 
                                    class="form-control recalculate-price">
                                <?php
                                    foreach ($income_types as $key => $value):
                                        $is_selected = '';
                                        if ((!empty(Input::post("income_type")) &&
                                                Input::post("income_type") == $key) ||
                                            (empty(Input::post("income_type")) &&
                                                $lottery['income_type'] == $key)
                                        ) {
                                            $is_selected = ' selected="selected"';
                                        }
                                ?>
                                        <option value="<?= $key; ?>" <?= $is_selected; ?>>
                                            <?= $value; ?>
                                        </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                        
                    </div>

                    <div class="form-group insurance-only <?= $insured_hidden_class; ?>">
                        <label class="control-label" for="inputInsuredTiers">
                            <?= _("Insured tiers"); ?>:
                        </label>
                        <select required 
                                name="input[insured_tiers]" 
                                id="inputInsuredTiers" 
                                class="form-control recalculate-price">
                            <?php
                                foreach ($type_data as $key => $type):
                            ?>
                                    <option value="<?= $key + 1; ?>" <?= $type['option_attributes']; ?>>
                                        <?= $type['value']; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                        <p class="help-block">
                            <?= $help_block_tiers; ?>
                        </p>
                    </div>
                    
                    <div class="panel panel-default insurance-only <?= $sample_calc_hidden_class; ?>">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <?= _("Sample calculation"); ?>
                            </h3>
                        </div>
                        <div class="panel-body">
                            <p>
                                <?= _("Real price may differ depending on the current jackpot and actual volume."); ?>
                            </p>
                            <div class="form-group <?= $sample_calc_jackpot_error_class; ?>">
                                <label class="control-label" for="inputJackpot">
                                    <?= _("Expected jackpot (in millions)"); ?>:
                                </label>
                                <input type="text" 
                                       value="<?= $sample_calc_jackpot_value; ?>"
                                       class="form-control recalculate-price" 
                                       id="inputJackpot" 
                                       name="input[jackpot]" 
                                       placeholder="<?= _("Enter expected jackpot"); ?>">
                            </div>
                            <div class="form-group <?= $sample_calc_volume_error_class; ?>">
                                <label class="control-label" for="inputVolume">
                                    <?= _("Expected volume"); ?>:
                                </label>
                                <input type="text" 
                                       value="<?= $sample_calc_volume_value; ?>" 
                                       class="form-control recalculate-price" 
                                       id="inputVolume" 
                                       name="input[volume]" 
                                       placeholder="<?= _("Enter expected volume"); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <h4>
                        <?= _("Price summary"); ?>
                    </h4>
                    <?= _("Ticket price"); ?>: <span id="ticketPrice">...</span>
                    <br>
                    <div id="winnings-container" class="hidden">
                        <?= _("Winnings per ticket"); ?>: <span id="winnings">...</span>
                    </div>
                    <div id="totalCost-container" class="hidden">
                        <?= _("Total cost"); ?>: <span id="totalCost">...</span>
                    </div>
                    <div id="ticketFee-container" class="hidden purchase-only">
                        <?= _("Ticket fee"); ?>: <span id="ticketFee">...</span>
                    </div>
                    <?= _("Expected income"); ?>: <span id="income">...</span>
                    <div id="propFinalPrice-container" class="hidden">
                        <?= _("Calculated final price"); ?>: <span id="propFinalPrice">...</span>
                    </div>
                    <div id="minPrice-container" class="hidden">
                        <?= _("Minimum price for users"); ?>: <span id="minPrice"><?= $min_price_for_user; ?></span>
                    </div>
                    <div id="finalIncome-container" class="hidden">
                        <?= _("Final income"); ?>: <span id="finalIncome">...</span>
                    </div>
                    <p>
                        <strong>
                            <?= _("Final price"); ?>:
                            <span id="calculatedPrice"
                                    data-siterate="<?= $default_currency_tab['rate']; ?>"
                                    data-sitecurrency="<?= $default_currency_tab['code']; ?>" 
                                    data-rate="<?= $currencies[$lottery['currency_id']]['rate']; ?>" 
                                    data-margin="<?= $whitelabel['margin']; ?>"
                                    data-ticketprice="<?= $lottery['price']; ?>" 
                                    data-ticketfee="<?= $lottery['fee']; ?>" 
                                    data-lottery="<?= $lottery['id']; ?>" 
                                    data-currency="<?= $lottery['currency']; ?>"
                                >...</span> <span id="priceSite-container">(<span id="calculatedPriceSite"></span>)</span>
                        </strong>
                    </p>

                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </div>
                
                <div class="col-md-6">
                    <h3 class="nmt">
                        <?= _("Other settings"); ?>
                    </h3>
                    <strong>
                        <?= _("Lottery status"); ?>:
                    </strong>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   name="input[enabled]"
                                   value="1" <?= $lottery_status_checked; ?>><?= _("Enabled"); ?>
                        </label>
                        <p class="help-block">
                            <?= _("All tickets bought before disabling a lottery will be processed."); ?>
                        </p>
                    </div>

                    <div class="form-group <?= $bonus_balance_purchase_limit_per_user_error_class; ?>">
                        <label class="control-label" for="bonusBalancePurchaseLimitPerUser">
                            <?= _("Bonus balance purchase"); ?>:
                        </label>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                       name="input[is_bonus_balance_in_use]"
                                       value="1" <?= $lottery_bonus_balance_checked; ?>><?= _("Can be purchased with bonus balance"); ?>
                            </label>
                        </div>
                        <input type="text"
                               required="required"
                               value="<?= $bonus_balance_purchase_limit_value; ?>"
                               class="form-control"
                               id="inputBonusBalancePurchaseLimitPerUser"
                               name="input[bonusBalancePurchaseLimitPerUser]"
                               placeholder="<?= _("Enter bonus balance daily purchase limit per user"); ?>">
                            <p class="help-block">
                                <?= _('Daily limit for how many ticket lines user can purchase for this lottery. Set to 0 for unlimited. Limits reset at 00:00 UTC.' ); ?>
                            </p>
                    </div>

                    <?php if ($lottery['is_multidraw_enabled'] == 1): ?>
                    <strong>
                        <?= _("Multi-draws"); ?>:
                    </strong>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   name="input[multidraws_enabled]"
                                   value="1" <?= $multidraws_status_checked; ?>><?= _("Enabled"); ?>
                        </label>
                    </div>
                    <?php endif; ?>
                    <div class="form-group <?= $min_lines_error_class; ?>">
                        <label class="control-label" for="inputMinLines">
                            <?= _("Minimum Lines"); ?>:
                        </label>
                        <input type="text" 
                               required="required" 
                               value="<?= $min_lines_value; ?>"
                               class="form-control" 
                               id="inputMinLines" 
                               name="input[minlines]" 
                               placeholder="<?= _("Enter minimum lines count"); ?>">
                        <?php if ($min_bets && $max_bets): ?>
                            <p class="help-block">
                                <?= sprintf(
                                _('Minimum lines should be equal to %d or higher, but not exceed %d'),
                                $min_bets,
                                Forms_Whitelabel_Lottery_Settings_Edit::MAX_QUICK_PICK_VALUE
                            ); ?>
                            </p>
                        <?php endif; ?>
                        <p class="help-block hidden" id="minlines-help">
                            <?= $help_block_multipier; ?>
                        </p>
                    </div>
                    <div class="form-group <?= $quick_pick_lines_error_class; ?>">
                        <label class="control-label" for="inputQuickPickLines">
                            <?= _("Mobile Quick-Pick Lines"); ?>:
                        </label>
                        <input type="text"
                               required="required"
                               value="<?= $quick_pick_lines_value; ?>"
                               class="form-control"
                               id="inputQuickPickLines"
                               name="input[quick_pick_lines]"
                               placeholder="<?= _("Enter mobile Quick-Pick lines"); ?>"
                        >
                        <?php

                            $min_quick_pick = $min_bets > $min_lines_value ? $min_bets : $min_lines_value;
                            $message = $multiplier == 0
                                ? _('Quick-Pick should be equal to %d or higher, but not exceed %d.')
                                : _('Quick-Pick should be equal to %d or higher, but not exceed %d. It should be multiple of %d.');

                            if ($min_bets && $max_bets):
                        ?>
                            <p class="help-block">
                                <?= sprintf(
                                  $message,
                            $min_quick_pick,
                            Forms_Whitelabel_Lottery_Settings_Edit::MAX_QUICK_PICK_VALUE,
                            $multiplier
                        ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="inputMultiplier">
                            <?= _("Mobile Quick-Pick Lines Second Widget"); ?>:
                        </label>
                        <input type="text"
                               value="<?= $second_quick_pick_lines_value; ?>"
                               class="form-control"
                               disabled
                        />
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


