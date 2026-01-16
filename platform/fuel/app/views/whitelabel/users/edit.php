<?php
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php
            include(APPPATH . "views/whitelabel/users/menu.php");
        ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Edit details"); ?> <small><?= Security::htmlentities($user['email']); ?></small>
        </h2>
		<p class="help-block">
            <?= _("Edit user details here."); ?>
        </p>
		<a href="/users/view/<?= $user['token']; ?><?= Lotto_View::query_vars(); ?>" 
           class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" action="/users/edit/<?= $user['token']; ?><?= Lotto_View::query_vars(); ?>">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                    
                        $name_class_error = "";
                        if (isset($errors['input.name'])) {
                            $name_class_error = ' has-error';
                        }
                        $name_value = "";
                        if (null !== Input::post("input.name")) {
                            $name_value = Input::post("input.name");
                        } elseif (!empty($user['name'])) {
                            $name_value = $user['name'];
                        }
                    ?>
                    <div class="form-group<?= $name_class_error; ?>">
                        <label class="control-label" for="inputName">
                            <?= _("First Name"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($name_value); ?>" 
                               class="form-control" id="inputName" name="input[name]" 
                               placeholder="<?= _("User first name"); ?>">
                    </div>
                    
                    <?php
                        $surname_class_error = "";
                        if (isset($errors['input.surname'])) {
                            $surname_class_error = ' has-error';
                        }
                        $surname_value = "";
                        if (null !== Input::post("input.surname")) {
                            $surname_value = Input::post("input.surname");
                        } elseif (!empty($user['surname'])) {
                            $surname_value = $user['surname'];
                        }
                    ?>
                    <div class="form-group<?= $surname_class_error; ?>">
                        <label class="control-label" for="inputSurname">
                            <?= _("Last Name"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($surname_value); ?>" 
                               class="form-control" id="inputSurname" name="input[surname]" 
                               placeholder="<?= _("User last name"); ?>">
                    </div>
                    
                    <?php
                        $country_class_error = "";
                        if (isset($errors['input.country'])) {
                            $country_class_error = ' has-error';
                        }
                    ?>
                    <div class="form-group<?= $country_class_error; ?>">
                        <label class="control-label" for="inputCountry">
                            <?= _("Country"); ?>:
                        </label>
                        <select name="input[country]" id="inputCountry" class="form-control">
                            <option value="">
                                <?= _("Choose user country"); ?>
                            </option>
                            <?php
                                foreach ($countries as $key => $country):
                                    $country_selected = "";
                                    if ((Input::post("input.country") !== null &&
                                            Input::post("input.country") == $key) ||
                                        (Input::post("input.country") === null &&
                                            $key == $user['country'])
                                    ) {
                                        $country_selected = ' selected="selected"';
                                    }
                            ?>
                                <option value="<?= $key; ?>"<?= $country_selected; ?>>
                                    <?= Security::htmlentities($country); ?>
                                </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>
                    
                    <?php
                        $city_class_error = "";
                        if (isset($errors['input.city'])) {
                            $city_class_error = ' has-error';
                        }
                        $city_value = "";
                        if (null !== Input::post("input.city")) {
                            $city_value = Input::post("input.city");
                        } elseif (!empty($user['city'])) {
                            $city_value = $user['city'];
                        }
                    ?>
                    <div class="form-group<?= $city_class_error; ?>">
                        <label class="control-label" for="inputCity">
                            <?= _("City"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($city_value); ?>" 
                               class="form-control" id="inputCity" name="input[city]" 
                               placeholder="<?= _("User city"); ?>">
                    </div>
                    
                    <?php
                        $state_class_error = "";
                        if (isset($errors['input.state'])) {
                            $state_class_error = ' has-error';
                        }
                        
                        $state_disabled = ' disabled';
                        $state_value = "";
                        if (Input::post("input.country") !== null) {
                            $state_value = Input::post("input.country");
                        } elseif (!empty($user['country'])) {
                            $state_value = $user['country'];
                        }
                        if (!empty($state_value)) {
                            $check_state = Lotto_View::regions_exist($state_value);
                            if ($check_state) {
                                $state_disabled = "";
                            }
                        }
                        
                    ?>
                    <div class="form-group<?= $state_class_error; ?>">
                       <label class="control-label" for="inputState">
                           <?= _("Region"); ?>:
                       </label>
                       <select name="input[state]" id="inputState" 
                               class="form-control"<?= $state_disabled; ?>>
                           <option value="">
                               <?= _("Choose user region"); ?>
                           </option>
                           <?php Lotto_View::generate_region_list($user['state'], $state_value); ?>
                       </select>
                       <select id="allRegions">
                           <option value="">
                               <?= _("Choose user region"); ?>
                           </option>
                           <?php Lotto_View::generate_region_list($user['state']); ?>
                       </select>
                    </div>
                    
                    <?php
                        $zip_class_error = "";
                        if (isset($errors['input.zip'])) {
                            $zip_class_error = ' has-error';
                        }
                        $zip_value = "";
                        if (null !== Input::post("input.zip")) {
                            $zip_value = Input::post("input.zip");
                        } elseif (!empty($user['zip'])) {
                            $zip_value = $user['zip'];
                        }
                    ?>
                    <div class="form-group<?= $zip_class_error; ?>">
                        <label class="control-label" for="inputZip">
                            <?= _("Postal/ZIP Code"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($zip_value); ?>" 
                               class="form-control" id="inputZip" name="input[zip]" 
                               placeholder="<?= _("User Postal/ZIP Code"); ?>">
                    </div>
                    
                    <?php
                        $address_class_error = "";
                        if (isset($errors['input.address'])) {
                            $address_class_error = ' has-error';
                        }
                        $address_value = "";
                        if (null !== Input::post("input.address")) {
                            $address_value = Input::post("input.address");
                        } elseif (!empty($user['address_1'])) {
                            $address_value = $user['address_1'];
                        }
                    ?>
                    <div class="form-group<?= $address_class_error; ?>">
                        <label class="control-label" for="inputAddress">
                            <?= _("Address #1"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($address_value); ?>" 
                               class="form-control" id="inputAddress" name="input[address]" 
                               placeholder="<?= _("User address #1"); ?>">
                    </div>
                    
                    <?php
                        $address2_class_error = "";
                        if (isset($errors['input.address_2'])) {
                            $address2_class_error = ' has-error';
                        }
                        $address2_value = "";
                        if (null !== Input::post("input.address_2")) {
                            $address2_value = Input::post("input.address_2");
                        } elseif (!empty($user['address_2'])) {
                            $address2_value = $user['address_2'];
                        }
                    ?>
                    <div class="form-group<?= $address2_class_error; ?>">
                        <label class="control-label" for="inputAddress2">
                            <?= _("Address #2"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($address2_value); ?>" 
                               class="form-control" id="inputAddress2" name="input[address_2]"
                               placeholder="<?= _("User address #2"); ?>">
                    </div>
                    
                    <?php
                        $birthdate_class_error = "";
                        if (isset($errors['input.birthdate'])) {
                            $birthdate_class_error = ' has-error';
                        }
                        $birthdate_value = "";
                        if (null !== Input::post("input.birthdate")) {
                            $birthdate_value = Input::post("input.birthdate");
                        } elseif (!empty($user['birthdate'])) {
                            $birthdate = new DateTime($user['birthdate'], new DateTimeZone("UTC"));
                            $birthdate_value = $birthdate->format('m/d/Y');
                        }
                    ?>
                    <div class="form-group<?= $birthdate_class_error; ?>">
                        <label class="control-label" for="inputBirthdate">
                            <?= _("Birthdate"); ?>:
                        </label>
                        <?php  ?>
                        <input type="text" 
                               value="<?= Security::htmlentities($birthdate_value); ?>" 
                               class="form-control datepicker" id="inputBirthdate" name="input[birthdate]" 
                               placeholder="<?= _("User birthdate"); ?>" 
                               data-date-start-date="-110y" data-date-end-date="-18y">
                        <p class="help-block">
                            <?= _("Format: mm/dd/yyyy"); ?>
                        </p>
                    </div>
                    
                    <?php
                        $prefix_class_error = "";
                        if (isset($this->errors['input.prefix'])) {
                            $prefix_class_error = ' has-error';
                        }
                        
                        $phone_country_code = "";
                        if (!empty($user['phone']) &&
                            !empty($user['phone_country'])
                        ) {
                            $phone_country_code_check = Lotto_View::get_phone_country_code(
                                $user['phone'],
                                $user['phone_country']
                            );
                            if (!is_null($phone_country_code_check)) {
                                $phone_country_code = $phone_country_code_check;
                            }
                        }
                    ?>
                    <div class="form-group<?= $prefix_class_error; ?>">
                        <label class="control-label" for="inputPrefix">
                            <?= _("Phone prefix"); ?>:
                        </label>
                        <select name="input[prefix]" id="inputPrefix" class="form-control">
                            <option value="">
                                <?= _("Choose a phone prefix"); ?>
                            </option>
                            <?php
                                foreach ($pcountries as $key => $country):
                                    if (isset($prefixes[$key])):
                                        foreach ($prefixes[$key] as $item):
                                            $value_phone_prefix_temp = $key;
                                            $value_phone_prefix_temp .= "_";
                                            $value_phone_prefix_temp .= $item;
                                            $value_phone_prefix = Security::htmlentities($value_phone_prefix_temp);
                                        
                                            $territory_phone_prefix = Security::htmlentities($key);
                                            
                                            $option_selected = '';
                                            if ((Input::post("input.prefix") !== null &&
                                                    stripslashes(Input::post("input.prefix")) == $value_phone_prefix_temp) ||
                                                (Input::post("input.prefix") === null &&
                                                    !empty($user['phone']) &&
                                                    $user['phone_country'] == $key &&
                                                    !empty($phone_country_code) &&
                                                    $phone_country_code == $item) ||
                                                (Input::post("input.prefix") === null &&
                                                    empty($user['phone']) &&
                                                    !empty($user['country']) &&
                                                    $user['country'] == $key)
                                            ) {
                                                $option_selected = ' selected="selected"';
                                            }
                                            
                                            $value_phone_prefix_text = "";
                                            if (isset($countries) && (isset($countries[$key]))) {
                                                $value_phone_prefix_text = $countries[$key] . " (+" . $item . ")";
                                            } else {
                                                $value_phone_prefix_text = '+' . $item;
                                            }
                                            $phone_prefix_text = Security::htmlentities($value_phone_prefix_text);
                            ?>
                                            <option value="<?= $value_phone_prefix; ?>" 
                                                    data-territory="<?= $territory_phone_prefix; ?>"
                                                    <?= $option_selected; ?>>
                                                        <?= $phone_prefix_text; ?>
                                            </option>
                                <?php
                                        endforeach;
                                    endif;
                                endforeach;
                            ?>
                        </select>
                    </div>
                    
                    <?php
                        $phone_class_error = "";
                        if (isset($errors['input.phone'])) {
                            $phone_class_error = ' has-error';
                        }
                        $phone_value = "";
                        if (null !== Input::post("input.phone")) {
                            $phone_value = Input::post("input.phone");
                        } elseif (!empty($user['address_2'])) {
                            $phone_value = Lotto_View::format_phone($user['phone'], $user['phone_country'], true);
                        }
                    ?>
                    <div class="form-group<?= $phone_class_error; ?>">
                        <label class="control-label" for="inputPhone">
                            <?= _("Phone"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($phone_value); ?>" 
                               class="form-control" id="inputPhone" name="input[phone]" 
                               placeholder="<?= _("User phone"); ?>">
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
                            <option value="">
                                <?= _("Choose time zone"); ?>
                            </option>
                            <?php
                                foreach ($timezones as $key => $timezone):
                                    $timezone_option_selected = "";
                                    if ((Input::post("input.timezone") !== null &&
                                            Input::post("input.timezone") == $key) ||
                                        (Input::post("input.timezone") === null &&
                                            !empty($user['timezone']) &&
                                            ($key == $user['timezone']))
                                    ) {
                                        $timezone_option_selected = ' selected="selected"';
                                    }
                            ?>
                                    <option value="<?= $key; ?>" <?= $timezone_option_selected; ?>>
                                            <?= Security::htmlentities($timezone); ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group<?= $has_error('gender') ?>">
                        <label class="control-label" for="inputGender">
                            <?= _("Gender") ?>:
                        </label>
                        <select name="input[gender]" id="inputGender" class="form-control">
                            <?php
                                foreach (Helpers_Fuel_Resources::get_genders() as $key => $gender):
                            ?>
                                    <option value="<?= $key; ?>" <?= $selected('gender', $key) ?>>
                                            <?= Security::htmlentities($gender); ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>

                    <div class="form-group<?= $has_error('national_id') ?>">
                        <label class="control-label" for="inputNationalId">
                            <?= _("National ID"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= $last_value('national_id') ?>" 
                               class="form-control" id="inputNationalId" name="input[national_id]" 
                               placeholder="<?= _("User national ID"); ?>">
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
