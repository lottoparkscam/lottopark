<?php include(APPPATH . "views/whitelabel/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Edit account settings"); ?>
        </h2>
		<p class="help-block">
            <?= _("You can edit your account settings here."); ?>
        </p>
		<a href="/account" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" action="/account/edit">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                        
                        $name_class_error = "";
                        if (isset($errors['input.name']) ||
                            isset($errors['loginemail'])
                        ) {
                            $name_class_error = ' has-error';
                        }
                        
                        $name_value = "";
                        if (null !== Input::post("input.name")) {
                            $name_value = Input::post("input.name");
                        } elseif (!empty($whitelabel['username'])) {
                            $name_value = $whitelabel['username'];
                        }
                    ?>
                    <div class="form-group<?= $name_class_error; ?>">
                        <label class="control-label" for="inputName">
                            <?= _("Username"); ?>:
                        </label>
                        <input type="text" autofocus 
                               value="<?= Security::htmlentities($name_value); ?>" 
                               class="form-control" id="inputName" name="input[name]" 
                               placeholder="<?= _("Enter username"); ?>">
                    </div>
                    
                    <?php
                        $realname_class_error = "";
                        if (isset($errors['input.realname'])) {
                            $realname_class_error = ' has-error';
                        }
                    
                        $realname_value = "";
                        if (null !== Input::post("input.realname")) {
                            $realname_value = Input::post("input.realname");
                        } elseif (!empty($whitelabel['realname'])) {
                            $realname_value = $whitelabel['realname'];
                        }
                    ?>
                    <div class="form-group<?= $realname_class_error; ?>">
                        <label class="control-label" for="inputRealName">
                            <?= _("Name"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($realname_value); ?>" 
                               class="form-control" id="inputRealName" name="input[realname]" 
                               placeholder="<?= _("Enter realname"); ?>">
                    </div>
                    
                    <?php
                        $email_class_error = "";
                        if (isset($errors['input.email']) ||
                            isset($errors['loginemail'])
                        ) {
                            $email_class_error = ' has-error';
                        }
                        
                        $email_value = "";
                        if (null !== Input::post("input.email")) {
                            $email_value = Input::post("input.email");
                        } elseif (!empty($whitelabel['email'])) {
                            $email_value = $whitelabel['email'];
                        }
                    ?>
                    <div class="form-group<?= $email_class_error; ?>">
                        <label class="control-label" for="inputEmail">
                            <?= _("E-mail"); ?>:
                        </label>
                        <input type="email" 
                               value="<?= Security::htmlentities($email_value); ?>" 
                               class="form-control" id="inputEmail" name="input[email]" 
                               placeholder="<?= _("Enter e-mail"); ?>">
                    </div>
                    
                    <?php
                        $language_class_error = "";
                        if (isset($errors['input.language'])) {
                            $language_class_error = ' has-error';
                        }
                    ?>
                    <div class="form-group<?= $language_class_error; ?>">
                        <label class="control-label" for="inputLanguage">
                            <?= _("Language"); ?>:
                        </label>
                        <select name="input[language]" id="inputLanguage" class="form-control">
                            <?php
                                foreach ($languages as $language):
                                    $language_selected = "";
                                    if ((Input::post("input.language") !== null &&
                                            Input::post("input.language") == $language['id']) ||
                                        (Input::post("input.language") === null &&
                                            $language['id'] == $whitelabel['language_id'])
                                    ) {
                                        $language_selected = ' selected="selected"';
                                    }
                            ?>
                                    <option value="<?= $language['id']; ?>" <?= $language_selected; ?>>
                                        <?= Lotto_View::format_language($language['code']); ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>
                    
                    <?php
                        $timezone_class_error = "";
                        if (isset($errors['input.timezone'])) {
                            $timezone_class_error = ' has-error';
                        }
                    ?>
                    <div class="form-group<?= $timezone_class_error; ?>">
                        <label class="control-label" for="inputTimezone">
                            <?= _("Time Zone"); ?>:
                        </label>
                        <select name="input[timezone]" id="inputTimezone" class="form-control">
                            <?php
                                foreach ($timezones as $key => $timezone):
                                    $timezone_selected = "";
                                    if ((Input::post("input.timezone") !== null &&
                                            Input::post("input.timezone") == $key) ||
                                        (Input::post("input.timezone") === null &&
                                            ($key == $whitelabel['timezone'] ||
                                                (empty($whitelabel['timezone']) &&
                                                    $key == "UTC")))
                                    ) {
                                        $timezone_selected = ' selected="selected"';
                                    }
                            ?>
                                    <option value="<?= $key; ?>"<?= $timezone_selected; ?>>
                                        <?= Security::htmlentities($timezone); ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>
                    
                    <?php
                        if (!Helpers_Whitelabel::is_V1($whitelabel['type']) ||
                            Helpers_Whitelabel::is_special_ID($whitelabel['id'])
                        ):
                            $max_order_class_error = "";
                            if (isset($errors['input.maxorderitems'])) {
                                $max_order_class_error = ' has-error';
                            }

                            $max_order_items_value = "";
                            if (null !== Input::post("input.maxorderitems")) {
                                $max_order_items_value = Input::post("input.maxorderitems");
                            } elseif (!empty($whitelabel['max_order_count'])) {
                                $max_order_items_value = $whitelabel['max_order_count'];
                            }
                    ?>
                            <div class="form-group<?= $max_order_class_error; ?>">
                                <label class="control-label" for="inputMaxOrderCount">
                                    <?= _("Maximum order items"); ?>:
                                </label>
                                <input type="number" 
                                       value="<?= Security::htmlentities($max_order_items_value); ?>" 
                                       class="form-control" 
                                       id="inputMaxOrderCount" 
                                       name="input[maxorderitems]" 
                                       placeholder="<?= _("Enter maximum order items count"); ?>">
                            </div>
                    <?php
                        endif;
                        
                        if ($edit_manager_currency):
                            $manager_currency_class_error = "";
                            if (isset($errors['input.managercurrency'])) {
                                $manager_currency_class_error = ' has-error';
                            }
                    ?>
                            <div class="form-group <?= $manager_currency_class_error; ?>">
                                <label class="control-label" for="inputManagerCurrency">
                                    <?= _("Manager currency"); ?>:
                                </label>
                                <select name="input[managercurrency]" id="inputManagerCurrency" class="form-control">
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
                                            <option value="<?= $key; ?>" <?= $current_selected; ?>>
                                                <?= $currency; ?>
                                            </option>
                                    <?php
                                        endforeach;
                                    ?>
                                </select>
                                <p class="help-block">
                                    <?php
                                        $help_block_text = _("Display currency for this management panel.");
                                        echo $help_block_text;
                                    ?>
                                </p>
                            </div>
                    <?php
                        endif;
                    ?>
                    
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
				</form>
			</div>
        </div>
    </div>
</div>
</div>
