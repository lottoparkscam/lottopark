<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
            include(APPPATH . "views/whitelabel/bonuses/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("New Promo Codes"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("You can add new promo codes bonus here."); ?>
        </p>

        <a href="/bonuses/promocodes<?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form id="form-promocodes" method="post" action="/bonuses/promocodes/new<?= Lotto_View::query_vars(); ?>">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                            
                        $group_error_class = '';
                        if (isset($errors['input.group'])) {
                            $group_error_class = ' has-error';
                        }
                    ?>

                    <div class="panel panel-default">
                        <div class="panel-body">
                            <?php
                                $purchase_checked = false;
                                $deposit_checked = false;
                                $register_checked = false;
                                if (Input::post("input.purchase") === "1") {
                                    $purchase_checked = true;
                                }
                                if (Input::post("input.deposit") === "1") {
                                    $deposit_checked = true;
                                }
                                if (Input::post("input.register") === "1") {
                                    $register_checked = true;
                                }
                            ?>
                            <label><?= _("Select where promo code can be used:") ?></label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="input-purchase" name="input[purchase]" <?= $purchase_checked ? 'checked' : '' ?> value="1">
                                    <?= _("Purchase") ?>    
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="input-deposit" name="input[deposit]" <?= $deposit_checked ? 'checked' : '' ?> value="1">
                                    <?= _("Deposit") ?>    
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="input-register" name="input[register]" <?= $register_checked ? 'checked' : '' ?> value="1">
                                    <?= _("Register") ?>    
                                </label>
                            </div>
                        
                            <div class="form-group" id="aff-list">
                                <?php
                                $affText = '';
                                if (Input::post('input.aff') !== null) {
                                    $affText = Input::post('input.aff');
                                }
                                $affText = Security::htmlentities($affText);
                                
                                ?>
                                <label class="control-label" for="inputAffiliate"><?= _("Affiliate (optional):") ?></label>
                                <input type="email" 
                                    value="<?= $affText; ?>" 
                                    class="form-control" 
                                    id="inputAffiliate" 
                                    name="input[aff]" 
                                    placeholder="<?= _("E-mail"); ?>">
                                <p class="help-block">
                                    <?= _("Select to which affiliate user will be connected after he uses the promo code during registration."); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-body">
                            <?php
                                $single_checked = true;
                                $series_checked = false;
                                if (Input::post("input.codes_type") === "1") {
                                    $single_checked = false;
                                    $series_checked = true;
                                }
                            ?>
                            <div class="form-group"> 
                                <label><?= _("Code type:") ?></label>
                                <div class="radio">
                                    <label class="weight-normal">
                                        <input type="radio" <?= $single_checked ? "checked" : "" ?> value="0" id="inputSingleCode" name="input[codes_type]">                        
                                        <?= _("Single code") ?>
                                    </label>
                                </div>
                                <div class="radio">
                                    <label class="weight-normal">
                                        <input type="radio" <?= $series_checked ? "checked" : "" ?> value="1" id="inputSeries" name="input[codes_type]">                        
                                        <?= _("Series") ?>
                                    </label>
                                </div>
                            </div>

                            <?php
                                $code_value_t = '';
                                if (!is_null(Input::post("input.code"))) {
                                    $code_value_t = Input::post("input.code");
                                }
                                $code_value = Security::htmlentities($code_value_t);
                                $code_length_value_t = '';
                                if (!is_null(Input::post("input.code_length"))) {
                                    $code_length_value_t = Input::post("input.code_length");
                                }
                                $code_length_value = Security::htmlentities($code_length_value_t);
                                $codes_num_value_t = '';
                                if (!is_null(Input::post("input.codes_num"))) {
                                    $codes_num_value_t = Input::post("input.codes_num");
                                }
                                $codes_num_value = Security::htmlentities($codes_num_value_t);
                                $codes_user_num_value_t = '';
                                if (!is_null(Input::post("input.codes_user_num"))) {
                                    $codes_user_num_value_t = Input::post("input.codes_user_num");
                                }
                                $codes_user_num_value = Security::htmlentities($codes_user_num_value_t);
                            ?>
                            <div class="form-group" id="input-code">
                                <label class="control-label" for="inputCode">
                                    <?= _("Code Prefix:") ?>
                                </label>
                                <input type="text" required autofocus class="form-control" id="inputCode" name="input[code]" placeholder="Enter code prefix" value="<?= $code_value; ?>">
                            </div>

                            <div class="form-group" id="input-series">                            
                                <label class="control-label" for="inputCodeLength">
                                    <?= _("Code length:") ?>
                                </label>
                                <input type="text" autofocus="" class="form-control m-b-15" id="inputCodeLength" name="input[code_length]" placeholder="Enter number of signs" value="<?= $code_length_value; ?>">

                                <label class="control-label" for="inputCodesNum">
                                    <?= _("Number of codes:") ?>
                                </label>
                                <input type="text" autofocus="" class="form-control m-b-15" id="inputCodesNum" name="input[codes_num]" placeholder="Enter number of codes" value="<?= $codes_num_value; ?>">
                            
                                <label class="control-label" for="inputCodesUserNum">
                                    <?= _("Number of codes one user can use:") ?>
                                </label>
                                <input type="text" autofocus="" class="form-control" id="inputCodesUserNum" name="input[codes_user_num]" placeholder="Enter number of codes" value="<?= $codes_user_num_value; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-body">
                            <?php
                                $ticket_checked = true;
                                $discount_checked = false;
                                $bonus_money_checked = false;
                                if (Input::post("input.bonus_type") === "1") {
                                    $ticket_checked = false;
                                    $discount_checked = true;
                                    $bonus_money_checked = false;
                                } elseif (Input::post("input.bonus_type") === "2") {
                                    $ticket_checked = false;
                                    $discount_checked = false;
                                    $bonus_money_checked = true;
                                }
                            ?>
                            <div class="form-group">  
                                <label class="control-label"><?= _("Bonus type:") ?></label>
                                <div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" <?= $ticket_checked ? "checked" : "" ?> autofocus="" value="0" id="inputFreeTicket" name="input[bonus_type]">                        
                                            <?= _("Free ticket") ?>
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" <?= $discount_checked ? "checked" : "" ?> autofocus="" value="1" id="inputDiscount" name="input[bonus_type]">                        
                                            <?= _("Discount") ?>
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" <?= $bonus_money_checked ? "checked" : "" ?> autofocus="" value="2" id="inputBonusMoney" name="input[bonus_type]">                        
                                            <?= _("Bonus money") ?>
                                        </label>
                                    </div>
                                </div>                        
                            </div>

                            <div class="form-group" id="input-lottery">
                                <label class="control-label" for="inputPromoCodeLottery">
                                    <?= _("Choose lottery"); ?>:
                                </label>
                                <select name="input[lottery]" id="inputPromoCodeLottery" class="form-control">
                                    <option value="0">
                                        <?= _("None") ?>
                                    </option>
                                    <?php
                                        foreach ($lotteries as $lottery):
                                            $is_selected = '';
                                            if ((Input::post("input.lottery") !== null &&
                                                Input::post("input.lottery") == $lottery['id'])
                                            ) {
                                                $is_selected = ' selected="selected"';
                                            }
                                    ?>
                                        <option value="<?= $lottery['id']; ?>"<?= $is_selected; ?>>
                                            <?= $lottery['name']; ?>
                                        </option>
                                    <?php
                                        endforeach;
                                    ?>
                                </select>
                            </div>

                            <?php
                                $percent_checked = true;
                                $amount_checked = false;
                                if (Input::post("input.discount_type") === "1") {
                                    $percent_checked = false;
                                    $amount_checked = true;
                                }
                                $amount_value_t = '';
                                if (!is_null(Input::post("input.amount"))) {
                                    $amount_value_t = Input::post("input.amount");
                                }
                                $amount_value = Security::htmlentities($amount_value_t);
                            ?>
                            <div class="form-group" id="input-discount">
                                <div>
                                    <label class="label-discount"><?= _("Discount type:") ?></label>
                                    <label class="label-bonus"><?= _("Amount type:") ?></label>
                                    <div class="radio">
                                        <label class="weight-normal">
                                            <input type="radio" <?= $percent_checked ? "checked" : "" ?> autofocus="" value="0" id="inputPercent" name="input[discount_type]">                        
                                            <?= _("%") ?>
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label class="weight-normal">
                                            <input type="radio" <?= $amount_checked ? "checked" : "" ?> autofocus="" value="1" id="inputAmount" name="input[discount_type]">                        
                                            <?= Lotto_View::format_currency_code($currency_code); ?>
                                        </label>
                                    </div> 
                                </div>
                                <label class="control-label label-discount" for="inputDiscountAmount">
                                    <?= _("Discount amount:") ?>
                                </label>
                                <label class="control-label label-bonus" for="inputDiscountAmount">
                                    <?= _("Bonus amount:") ?>
                                </label>
                                <input type="text" autofocus="" value="<?= $amount_value; ?>" class="form-control" id="inputDiscountAmount" name="input[amount]" placeholder="Enter amount">
                            </div>
                        </div>
                    </div>
                    
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <?php
                                $users_num_value_t = '';
                                if (!is_null(Input::post("input.users_num"))) {
                                    $users_num_value_t = Input::post("input.users_num");
                                }
                                $users_num_value = Security::htmlentities($users_num_value_t);
                            ?>
                            <div class="form-group">
                                <label class="control-label" for="inputUsersNum">
                                    <?= _("How many users can use one code:") ?>
                                </label>
                                <input type="text" autofocus="" value="<?= $users_num_value; ?>" class="form-control" id="inputUsersNum" name="input[users_num]" placeholder="Enter number of users">
                            </div>

                            <?php
                                $users_limit_value_t = '';
                                if (!is_null(Input::post("input.users_limit"))) {
                                    $users_limit_value_t = Input::post("input.users_limit");
                                }
                                $users_limit_value = Security::htmlentities($users_limit_value_t);
                            ?>
                            <div class="form-group">
                                <label class="control-label" for="inputUsersLimit">
                                    <?= _("Max number of users who can use this bonus:") ?>
                                </label>
                                <input type="text" autofocus="" value="<?= $users_limit_value; ?>" class="form-control" id="inputUsersLimit" name="input[users_limit]" placeholder="Enter number of users">
                                <p class="help-block">
                                    <?= _("Applies only to the promo codes series."); ?>
                                </p>
                            </div>

                            <?php
                                $start_date_value_t = '';
                                if (!is_null(Input::post("input.start_date"))) {
                                    $start_date_value_t = Input::post("input.start_date");
                                }
                                $start_date_value = Security::htmlentities($start_date_value_t);
                            ?>
                            <div class="form-group">
                                <label class="control-label" for="inputStartDate">
                                    <?= _("Start date"); ?>:
                                </label>
                                <?php  ?>
                                <input type="text" 
                                    value="<?= $start_date_value; ?>"
                                    class="form-control datepicker" id="inputStartDate" name="input[start_date]" 
                                    placeholder="<?= _("Start date"); ?>" >
                                <p class="help-block">
                                    <?= _("Format: mm/dd/yyyy"); ?>
                                </p>
                            </div>

                            <?php
                                $end_date_value_t = '';
                                if (!is_null(Input::post("input.end_date"))) {
                                    $end_date_value_t = Input::post("input.end_date");
                                }
                                $end_date_value = Security::htmlentities($end_date_value_t);
                            ?>
                            <div class="form-group">
                                <label class="control-label" for="inputEndDate">
                                    <?= _("End date"); ?>:
                                </label>
                                <?php  ?>
                                <input type="text" 
                                    value="<?= $end_date_value; ?>"
                                    class="form-control datepicker" id="inputEndDate" name="input[end_date]" 
                                    placeholder="<?= _("End date"); ?>" >
                                <p class="help-block">
                                    <?= _("Format: mm/dd/yyyy"); ?>
                                </p>
                            </div>
                            
                            <?php
                                $active_checked = false;
                                if (Input::post("input.is_active") === "1") {
                                    $active_checked = true;
                                }
                            ?>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="1" name="input[is_active]" <?= $active_checked ? 'checked' : '' ?>>
                                    <?= _("Enabled") ?>    
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <?= _("Submit"); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
