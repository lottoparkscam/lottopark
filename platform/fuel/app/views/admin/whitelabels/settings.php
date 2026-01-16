<?php 
include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/admin/whitelabels/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Whitelabel site settings"); ?> <small><?= $whitelabel['name']; ?></small>
        </h2>
		<p class="help-block">
            <?= _("You can change whitelabel site settings here."); ?>
        </p>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<?php include(APPPATH . "views/admin/shared/messages.php"); ?>
                   
				<form method="post" action="/whitelabels/settings/<?= $whitelabel['id']; ?>/">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                    ?>
                    <div class="form-group <?= $error_classes["type"]; ?>">
                        <label class="control-label" for="inputType">
                            <?= _("Activation Type"); ?>:
                        </label>
                        <select required name="input[type]" id="inputType" class="form-control">
                            <?php 
                                foreach ($activation_types as $activation_type):
                            ?>
                                    <option value="<?= $activation_type["key"]; ?>" 
                                            <?= $activation_type["selected"]; ?>>
                                        <?= $activation_type["text"]; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                        <p class="help-block">
                            <?= $help_block_text; ?>
                        </p>
                    </div>
                    
                    <?php
                        if ($full_form):
                    ?>
                            <div class="form-group<?= $error_classes["maxpayout"]; ?>">
                                <label class="control-label" for="inputMaxPayout">
                                    <?= _("Maximum Auto-Payout"); ?>:
                                </label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <?= $other_values["currency_code"]; ?>
                                    </div>
                                    <input type="text" 
                                           required="required" 
                                           autofocus 
                                           value="<?= $other_values["max_auto_payout"]; ?>" 
                                           class="form-control" 
                                           id="inputMaxPayout" 
                                           name="input[maxpayout]" 
                                           placeholder="<?= _("Enter maximum automatic payout"); ?>">
                                </div>
                            </div>
                    <?php
                        endif;
                    ?>
                    
                    <?php
                    $register_name_surname_class_error = "";
                    if (isset($errors['input.register_name_surname'])) {
                        $register_name_surname_class_error = ' has-error';
                    }
                    ?>

                    <div class="form-group<?= $register_name_surname_class_error; ?>">
                        <label class="control-label" for="register_name_surname">
                            <?= _("Show name and surname fields in registration form"); ?>:
                        </label>
                        <select required name="input[register_name_surname]" id="register_name_surname" class="form-control">
                            <?php
                            $types_table = [
                                _("No display") => Helpers_General::REGISTER_FIELD_NONE,
                                _("Optional") => Helpers_General::REGISTER_FIELD_OPTIONAL,
                                _("Required") => Helpers_General::REGISTER_FIELD_REQUIRED
                            ];
                            foreach ($types_table as $text_to_show => $type_key):
                                $is_selected = '';
                                if ((Input::post("input.register_name_surname") !== null && Input::post("input.register_name_surname") == $type_key) ||
                                    (!empty($whitelabel['register_name_surname']) &&
                                        in_array($whitelabel['register_name_surname'], $types_table) &&
                                        $whitelabel['register_name_surname'] == $type_key)
                                ) {
                                    $is_selected = ' selected="selected"';
                                }
                                ?>
                                <option value="<?= $type_key; ?>" <?= $is_selected; ?>>
                                    <?= $text_to_show; ?>
                                </option>
                            <?php
                            endforeach;
                            ?>
                        </select>
                    </div>

                    <?php
                    $register_phone_class_error = "";
                    if (isset($errors['input.register_phone'])) {
                        $register_phone_class_error = ' has-error';
                    }
                    ?>

                    <div class="form-group<?= $register_phone_class_error; ?>">
                        <label class="control-label" for="register_phone">
                            <?= _("Show phone field in registration form"); ?>:
                        </label>
                        <select required name="input[register_phone]" id="register_phone" class="form-control">
                            <?php
                            $types_table = [
                                _("No display") => Helpers_General::REGISTER_FIELD_NONE,
                                _("Optional") => Helpers_General::REGISTER_FIELD_OPTIONAL,
                                _("Required") => Helpers_General::REGISTER_FIELD_REQUIRED
                            ];
                            foreach ($types_table as $text_to_show => $type_key):
                                $is_selected = '';
                                if ((Input::post("input.register_phone") !== null && Input::post("input.register_phone") == $type_key) ||
                                    (!empty($whitelabel['register_phone']) &&
                                        in_array($whitelabel['register_phone'], $types_table) &&
                                        $whitelabel['register_phone'] == $type_key)
                                ) {
                                    $is_selected = ' selected="selected"';
                                }
                                ?>
                                <option value="<?= $type_key; ?>" <?= $is_selected; ?>>
                                    <?= $text_to_show; ?>
                                </option>
                            <?php
                            endforeach;
                            ?>
                        </select>
                    </div>

                    <?php
                    $welcome_popup_timeout_class_error = "";
                    if (isset($errors['input.welcome_popup_timeout'])) {
                        $welcome_popup_timeout_class_error = ' has-error';
                    }
                    ?>

                    <div class="form-group<?= $welcome_popup_timeout_class_error; ?>">
                        <label class="control-label" for="welcome_popup_timeout">
                            <?= _("First visit welcome popup timeout"); ?>:
                        </label>
                        <input type="number"
                               autofocus
                               value="<?= $whitelabel["welcome_popup_timeout"]; ?>"
                               class="form-control"
                               id="welcome_popup_timeout"
                               name="input[welcome_popup_timeout]"
                               placeholder="<?= _("Enter timeout in seconds"); ?>">
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" 
                               autofocus
                               id="show_ok_in_welcome_popup" value="1" 
                               name="input[show_ok_in_welcome_popup]"
                               <?= $whitelabel['show_ok_in_welcome_popup'] ? 'checked' : '' ?>>
                            <?= _('Show OK in welcome popup'); ?>
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
</div>
