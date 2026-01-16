<?php
include(APPPATH."views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH."views/admin/whitelabels/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Edit whitelabel"); ?> <small><?= Security::htmlentities($whitelabel['name']); ?>
        </h2>
        
        <p class="help-block">
            <?= _("You can edit whitelabel here."); ?>
        </p>
        
        <a href="/whitelabels" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" action="/whitelabels/edit/<?= $whitelabel['id']; ?>">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                    ?>

                    <div class="form-group <?= $error_name_class; ?>">
                        <label class="control-label" for="inputName">
                            <?= _("Name"); ?>:
                        </label>
                        <input type="text" 
                               required="required" 
                               value="<?= $name_value; ?>" 
                               class="form-control" 
                               id="inputName" 
                               name="input[name]" 
                               placeholder="<?= _("Enter site name"); ?>">
                    </div>
                    
                    <div class="form-group <?= $error_domain_class; ?>">
                        <label class="control-label" for="inputDomain">
                            <?= _("Domain"); ?>:
                        </label>
                        <input type="text" 
                               required="required" 
                               value="<?= $domain_value; ?>" 
                               class="form-control" 
                               id="inputDomain" 
                               name="input[domain]" 
                               placeholder="<?= _("Enter site domain"); ?>">
                        <p class="help-block">
                            <?php
                                $help_block_text = _(
                        "E.g. <strong>https://whitelabel.com</strong>. " .
                                                "Only lowercase letters (a-z), numbers, " .
                                                "and hyphens are allowed."
                                );
                                echo $help_block_text;
                            ?>
                        </p>
                    </div>

                    <div class="form-group <?= $error_company_class; ?>">
                        <label class="control-label" for="inputCompany">
                            <?= _("Company"); ?>:
                        </label>
                        <textarea
                            class="form-control"
                            id="inputCompany"
                            name="input[company]"
                            placeholder="<?= _("Enter company details"); ?>"><?= $company_value; ?></textarea>
                    </div>
                    
                    <div class="form-group <?= $error_email_class; ?>">
                        <label class="control-label" for="inputEmail">
                            <?= _("E-mail"); ?>:
                        </label>
                        <input type="email" 
                               required="required" 
                               value="<?= $email_value; ?>" 
                               class="form-control" 
                               id="inputEmail" 
                               name="input[email]" 
                               placeholder="<?= _("Enter client e-mail"); ?>">
                    </div>
                    
                    <div class="form-group <?= $error_realname_class; ?>">
                        <label class="control-label" for="inputRealname">
                            <?= _("Client name"); ?>:
                        </label>
                        <input type="text" 
                               required="required" 
                               value="<?= $realname_value; ?>" 
                               class="form-control" 
                               id="inputRealname" 
                               name="input[realname]" 
                               placeholder="<?= _("Enter client name"); ?>">
                    </div>
                    
                    <div class="form-group <?= $error_margin_class; ?>">
                        <label class="control-label" for="inputMargin">
                            <?= _("Margin"); ?>:
                        </label>
                       <div class="input-group">
                            <div class="input-group-addon">%</div>
                            <input type="text" 
                                   required="required" 
                                   value="<?= $margin_value; ?>" 
                                   class="form-control" 
                                   id="inputMargin" 
                                   name="input[margin]" 
                                   placeholder="<?= _("Enter site margin"); ?>">
                       </div>
                    </div>
                    
                    <?php
                        if (!Helpers_Whitelabel::is_V1($whitelabel['type'])):
                    ?>
                            <div class="form-group <?= $error_prepaid_alert_limit_class; ?>">
                                <label class="control-label" for="inputPrepaidAlertLimit">
                                    <?= _("Prepaid alert limit"); ?>:
                                </label>
                                <div class="input-group">
                                    <div class="input-group-addon" id="prepaidAlertLimitCurrencyCode">
                                        <?= $prepaid_currency_code; ?>
                                    </div>
                                    <input type="text" 
                                           required="required" 
                                           value="<?= $prepaid_alert_limit_value; ?>" 
                                           class="form-control" 
                                           id="inputPrepaidAlertLimit" 
                                           name="input[prepaid_alert_limit]" 
                                           placeholder="<?= _("Enter site prepaid alert limit"); ?>">
                                </div>
                                <p class="help-block">
                                    <?= $prepaid_alert_limit_help_text; ?>
                                </p>
                            </div>
                    <?php
                        endif;
                    ?>
                    
                    <div class="form-group <?= $error_username_class; ?>">
                        <label class="control-label" for="inputUsername">
                            <?= _("Username"); ?>:
                        </label>
                        <input type="text" 
                               required="required" 
                               value="<?= $username_value; ?>"
                               class="form-control" 
                               id="inputUsername" 
                               name="input[username]" 
                               placeholder="<?= _("Enter site username"); ?>">
                    </div>
                    
                    <div class="form-group <?= $error_passoword_class; ?>">
                        <label class="control-label" for="inputPassword">
                            <?= _("Password"); ?>:
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control clear" 
                                   id="inputPassword" 
                                   name="input[password]" 
                                   placeholder="<?= _("Enter password"); ?>">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" id="generatePassword">
                                    <span class="glyphicon glyphicon-refresh"></span> <?= _("Random"); ?>
                                </button>
                            </span>
                        </div>
                        <p class="help-block" id="generatedPassword">
                            <?= _("Generated password"); ?>: <span></span>
                        </p>
                        <p class="help-block">
                            <?= _("Leave blank to keep the current password"); ?>
                        </p>
                    </div>
                    
                    <?php
                        if (!Helpers_Whitelabel::is_V1($whitelabel['type']) ||
                            Helpers_Whitelabel::is_special_ID($whitelabel['id'])
                        ):
                    ?>
                            <div class="form-group <?= $max_order_class_error; ?>">
                                <label class="control-label" for="inputMaxOrderCount">
                                    <?= _("Maximum order items"); ?>:
                                </label>
                                <input type="number" 
                                       value="<?= $max_order_items_value; ?>" 
                                       class="form-control" 
                                       id="inputMaxOrderCount" 
                                       name="input[maxorderitems]" 
                                       placeholder="<?= _("Enter maximum order items count"); ?>">
                            </div>
                    <?php
                        endif;
                    ?>
                    
                    <div class="form-group <?= $manager_currency_class_error; ?>">
                        <label class="control-label" for="inputManagerCurrency">
                            <?= _("Manager site currency"); ?>:
                        </label>
                        <select name="input[managercurrency]" 
                                id="inputManagerCurrency" 
                                class="form-control">
                            <?php
                                foreach ($currencies as $key => $currency):
                                    $current_selected = '';
                                    if ((Input::post("input.managercurrency") !== null &&
                                            Input::post("input.managercurrency") == $key) ||
                                        (Input::post("input.managercurrency") === null &&
                                            $whitelabel['manager_site_currency_id'] == $key)
                                    ) {
                                        $current_selected = ' selected="selected"';
                                    }
                            ?>
                                    <option value="<?= $key; ?>" <?= $current_selected; ?> 
                                            data-code="<?= $currency; ?>">
                                        <?= $currency; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                        <p class="help-block">
                            <?php
                                $help_block_text = _(
                                "Display currency for management panel."
                            );
                                echo $help_block_text;
                            ?>
                        </p>
                        <p class="help-block">
                            <?php
                                $help_block_text = _(
                                "NOTICE! Be aware that change of this value " .
                                    "influence on the whole system!"
                            );
                                echo $help_block_text;
                            ?>
                        </p>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   name="input[us_state_active]"
                                   value="1"
                                   id="usStateActive"
                                <?= ($whitelabel['us_state_active'] == 1) ? 'checked' : ''; ?>
                            >
                            <?= _("Send US state information to L-Tech"); ?>
                        </label>
                    </div>
                    
                    <div class="form-group ">
                        <label class="control-label" for="enabledUsStates">
                            <?= _("Enabled US States"); ?>:
                        </label>
                        <select name="input[enabled_us_states][]"
                                id="enabledUsStates"
                                class="form-control multiple-select-bigger" multiple>
                            <?php
                                foreach ($us_states as $postal_code => $state):
                                    $us_state_selected = '';
                                    if ((Input::post("input.enabled_us_states") !== null &&
                                            in_array($postal_code, Input::post("input.enabled_us_states"))) ||
                                        (Input::post("input.enabled_us_states") === null &&
                                            in_array($postal_code, $whitelabel['enabled_us_states']))
                                    ) {
                                        $us_state_selected = ' selected="selected"';
                                    }
                            ?>
                                    <option value="<?= $postal_code; ?>" <?=$us_state_selected;?>>
                                        <?= $postal_code; ?> - <?= $state; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>
                    
                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   name="input[is_report]"
                                   value="1"
                                   id="isReport"
                                <?= $is_report_checked; ?>>
                            <?= _("Should be considered in reports"); ?>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

