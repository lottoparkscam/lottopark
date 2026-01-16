<?php
include(APPPATH."views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH."views/admin/whitelabels/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Add whitelabel"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("You can add new whitelabel here."); ?>
        </p>
        
        <a href="/whitelabels" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" action="/whitelabels/new">
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
                    
                    <div class="form-group <?= $error_themename_class; ?>">
                          <label class="control-label" for="inputThemeName">
                            <?= _("Wordpress theme name"); ?>:
                        </label>
                        <input type="text" 
                               required="required" 
                               value="<?= $themename_value; ?>" 
                               class="form-control" 
                               id="inputThemeName" 
                               name="input[themename]" 
                               placeholder="<?= _("Enter site theme name"); ?>">
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
                            <?= $domain_help_text; ?>
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
                    
                    <div class="form-group <?= $error_type_class; ?>">
                        <label class="control-label" for="inputWLType">
                            <?= _("Whitelabel Type"); ?>:
                        </label>
                        <select required name="input[type]" 
                                id="inputWLType" 
                                class="form-control">
                            <?php
                                foreach ($whitelabel_types as $type):
                            ?>
                                    <option value="<?= $type['id']; ?>" <?= $type['is_selected']; ?>>
                                        <?= $type['name']; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>
                    
                    <div class="hidden-normal form-group <?= $error_prepaid_class; ?>">
                        <label class="control-label" for="inputPrepaid">
                            <?= _("Prepaid"); ?>:
                        </label>
                        <div class="input-group">
                            <div class="input-group-addon" id="prepaidCurrencyCode">
                                <?= $prepaid_currency_code; ?>
                            </div>
                            <input type="text" 
                                   required="required" 
                                   value="<?= $prepaid_value; ?>" 
                                   class="form-control" 
                                   id="inputPrepaid" 
                                   name="input[prepaid]" 
                                   placeholder="<?= _("Enter site prepaid"); ?>">
                        </div>
                    </div>
                    
                    <div class="hidden-normal form-group <?= $error_prepaid_alert_limit_class; ?>">
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
                                   placeholder="<?= _("Enter prepaid alert limit"); ?>">
                        </div>
                        <p class="help-block">
                            <?= $prepaid_alert_limit_help_text; ?>
                        </p>
                    </div>
                    
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
                                   required="required" 
                                   class="form-control clear" 
                                   id="inputPassword" 
                                   name="input[password]" 
                                   placeholder="<?= _("Enter password"); ?>">
                            <span class="input-group-btn">
                                <button type="button" 
                                        class="btn btn-default" 
                                        id="generatePassword">
                                    <span class="glyphicon glyphicon-refresh"></span> <?= _("Random"); ?>
                                </button>
                            </span>
                        </div>
                        <p class="help-block" id="generatedPassword">
                            <?= _("Generated password"); ?>: <span></span>
                        </p>
                    </div>
                    
                    <div class="form-group <?= $error_prefix_class; ?>">
                        <label class="control-label" for="inputPrefix">
                            <?= _("Prefix"); ?>:
                        </label>
                        <input type="text" 
                               required="required" 
                               value="<?= $prefix_value; ?>" 
                               class="form-control" 
                               id="inputPrefix" 
                               name="input[prefix]" 
                               placeholder="<?= _("Enter site prefix"); ?>">
                        <p class="help-block">
                            <?= _("Should be 2-letters long, e.g. <strong>WL</strong>."); ?>
                        </p>
                    </div>
                    
                    <div class="form-group <?= $manager_currency_class_error; ?>">
                        <label class="control-label" for="inputManagerCurrency">
                            <?= _("Manager site currency"); ?>:
                        </label>
                        <select name="input[managercurrency]" 
                                id="inputManagerCurrency" 
                                class="form-control">
                            <?php
                                foreach ($manager_currencies as $currency):
                            ?>
                                    <option value="<?= $currency['id']; ?>" <?= $currency['is_selected']; ?> 
                                            data-code="<?= $currency['code']; ?>">
                                        <?= $currency['code']; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                        <p class="help-block">
                            <?php
                                $help_block_text = _("Display currency for management panel.");
                                echo $help_block_text;
                            ?>
                        </p>
                    </div>
                    
                    <div class="form-group <?= $site_currency_class_error; ?>">
                        <label class="control-label" for="inputSiteCurrency">
                            <?= _("Default site currency"); ?>:
                        </label>
                        <select name="input[sitecurrency]" 
                                id="inputSiteCurrency" 
                                class="form-control">
                            <?php
                                foreach ($site_currencies as $currency):
                            ?>
                                    <option value="<?= $currency['id']; ?>" <?= $currency['is_selected']; ?>>
                                        <?= $currency['code']; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                        <p class="help-block">
                            <?= $site_currency_help_block_text; ?>
                        </p>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   name="input[us_state_active]"
                                   value="1"
                                   id="usStateActive">
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
                            ?>
                                <option value="<?= $postal_code; ?>">
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

