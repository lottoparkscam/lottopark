<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH."views/aff/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Edit details"); ?> <small><?= Security::htmlentities($user['email']); ?></small>
        </h2>
        <p class="help-block">
            <?= _("Edit your details here."); ?>
        </p>
        <a href="/settings" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" action="/settings/edit">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/aff/shared/errors.php");
                        }
                        
                        $login = "";
                        if (null !== Input::post("input.login")) {
                            $login = Input::post("input.login");
                        } elseif (!empty($user['login'])) {
                            $login =  $user['login'];
                        }

                        $company = "";
                        if (null !== Input::post("input.company")) {
                            $company = Input::post("input.company");
                        } elseif (!empty($user['company'])) {
                            $company =  $user['company'];
                        }

                        $name = "";
                        if (null !== Input::post("input.name")) {
                            $name = Input::post("input.name");
                        } elseif (!empty($user['name'])) {
                            $name =  $user['name'];
                        }

                        $surname = "";
                        if (null !== Input::post("input.surname")) {
                            $surname = Input::post("input.surname");
                        } elseif (!empty($user['surname'])) {
                            $surname =  $user['surname'];
                        }

                        $city = "";
                        if (null !== Input::post("input.city")) {
                            $city = Input::post("input.city");
                        } elseif (!empty($user['city'])) {
                            $city =  $user['city'];
                        }

                        $country_input = "";
                        if (null !== Input::post("input.country")) {
                            $country_input = Input::post("input.country");
                        } elseif (!empty($user['country'])) {
                            $country_input =  $user['country'];
                        }

                        $zip = "";
                        if (null !== Input::post("input.zip")) {
                            $zip = Input::post("input.zip");
                        } elseif (!empty($user['zip'])) {
                            $zip =  $user['zip'];
                        }

                        $address1 = "";
                        if (null !== Input::post("input.address")) {
                            $address1 = Input::post("input.address");
                        } elseif (!empty($user['address_1'])) {
                            $address1 =  $user['address_1'];
                        }

                        $address2 = "";
                        if (null !== Input::post("input.address_2")) {
                            $address2 = Input::post("input.address_2");
                        } elseif (!empty($user['address_2'])) {
                            $address2 =  $user['address_2'];
                        }

                        $birthdate_input = "";
                        if (null !== Input::post("input.birthdate")) {
                            $birthdate_input = Input::post("input.birthdate");
                        } elseif (!empty($user['birthdate'])) {
                            $birthdate = new DateTime($user['birthdate'], new DateTimeZone("UTC"));
                            $birthdate_input =  $birthdate->format('m/d/Y');
                        }

                        $phone_input = "";
                        if (null !== Input::post("input.phone")) {
                            $phone_input = Input::post("input.phone");
                        } elseif (!empty($user['phone']) && !empty($user['phone_country'])) {
                            $phone_input = Lotto_View::format_phone($user['phone'], $user['phone_country'], true);
                        }

                    ?>
                    <div class="form-group<?php if (isset($errors['input.login'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputLogin">
                            <?= _("Login"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($login); ?>" 
                               class="form-control" 
                               id="inputLogin" 
                               name="input[login]" 
                               placeholder="<?= _("Your login"); ?>">
                    </div>
                    <div class="form-group<?php if (isset($errors['input.company'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputCompany">
                            <?= _("Company"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($company); ?>" 
                               class="form-control" 
                               id="inputCompany" 
                               name="input[company]" 
                               placeholder="<?= _("Your company"); ?>">
                    </div>
                    <div class="form-group<?php if (isset($errors['input.name'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputName">
                            <?= _("First Name"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($name); ?>" 
                               class="form-control" 
                               id="inputName" 
                               name="input[name]" 
                               placeholder="<?= _("Your first name"); ?>">
                    </div>
                    <div class="form-group<?php if (isset($errors['input.surname'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputSurname">
                            <?= _("Last Name"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($surname); ?>" 
                               class="form-control" 
                               id="inputSurname" 
                               name="input[surname]" 
                               placeholder="<?= _("Your last name"); ?>">
                    </div>
                    <div class="form-group<?php if (isset($errors['input.country'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputCountry">
                            <?= _("Country"); ?>:
                        </label>
                        <select name="input[country]" 
                                id="inputCountry" 
                                class="form-control">
                            <option value=""><?= _("Your country"); ?></option>
                            <?php
                                foreach ($countries as $key => $country):
                            ?>
                                    <option value="<?= $key; ?>"<?php if ((Input::post("input.country") !== null && Input::post("input.country") == $key) || (Input::post("input.country") === null && $key == $user['country'])): echo ' selected="selected"'; endif; ?>><?= Security::htmlentities($country); ?></option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>
                    <div class="form-group<?php if (isset($errors['input.city'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputCity">
                            <?= _("City"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($city); ?>" 
                               class="form-control" 
                               id="inputCity" 
                               name="input[city]" 
                               placeholder="<?= _("Your city"); ?>">
                    </div>
                    <div class="form-group<?php if (isset($errors['input.state'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputState">
                            <?= _("Region"); ?>:
                        </label>
                        <select name="input[state]" 
                                id="inputState" 
                                class="form-control"<?php if (!Lotto_View::regions_exist($country_input)): echo ' disabled'; endif; ?>>
                            <option value="">
                                <?= _("Your region"); ?>
                            </option>
                            <?php Lotto_View::generate_region_list($user['state'], $country_input); ?>
                        </select>
                        <select id="allRegions">
                            <option value=""><?= _("Your region"); ?></option>
                            <?php Lotto_View::generate_region_list($user['state']); ?>
                        </select>
                    </div>
                    <div class="form-group<?php if (isset($errors['input.zip'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputZip">
                            <?= _("Postal/ZIP Code"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($zip); ?>" 
                               class="form-control" 
                               id="inputZip" 
                               name="input[zip]" 
                               placeholder="<?= _("Your postal/ZIP code"); ?>">
                    </div>
                    <div class="form-group<?php if (isset($errors['input.address'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputAddress">
                            <?= _("Address #1"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($address1); ?>" 
                               class="form-control" 
                               id="inputAddress" 
                               name="input[address]" 
                               placeholder="<?= _("Your address #1"); ?>">
                    </div>
                    <div class="form-group<?php if (isset($errors['input.address_2'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputAddress2">
                            <?= _("Address #2"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($address2); ?>" 
                               class="form-control" 
                               id="inputAddress2" 
                               name="input[address_2]"
                               placeholder="<?= _("Your address #2"); ?>">
                    </div>
                    <div class="form-group<?php if (isset($errors['input.birthdate'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputBirthdate">
                            <?= _("Birthdate"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($birthdate_input); ?>" 
                               class="form-control datepicker" 
                               id="inputBirthdate" 
                               name="input[birthdate]" 
                               placeholder="<?= _("Your birthdate"); ?>" 
                               data-date-start-date="-110y" 
                               data-date-end-date="-18y">
                        <p class="help-block">
                            <?= _("Format: mm/dd/yyyy"); ?>
                        </p>
                    </div>
                    
                    <?php
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
                    <div class="form-group<?php if (isset($this->errors['input.prefix'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputPrefix">
                            <?= _("Phone prefix"); ?>:
                        </label>
                        <select name="input[prefix]" 
                                id="inputPrefix" 
                                class="form-control">
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
                                            
                                            $phone_prefix_selected = '';
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
                                                $phone_prefix_selected = ' selected="selected"';
                                            }
                                            
                                            $value_phone_prefix_text = "";
                                            if ((isset($countries[$key]))) {
                                                $value_phone_prefix_text = $countries[$key] . " (+" . $item . ")";
                                            } else {
                                                $value_phone_prefix_text = '+' . $item;
                                            }
                                            $phone_prefix_text = Security::htmlentities($value_phone_prefix_text);
                            ?>
                                            <option value="<?= $value_phone_prefix; ?>" 
                                                    data-territory="<?= $territory_phone_prefix; ?>"
                                                    <?= $phone_prefix_selected; ?>>
                                                        <?= $phone_prefix_text; ?>
                                            </option>
                            <?php
                                        endforeach;
                                    endif;
                                endforeach;
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group<?php if (isset($errors['input.phone'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputPhone">
                            <?= _("Phone"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Security::htmlentities($phone_input); ?>" 
                               class="form-control" 
                               id="inputPhone" 
                               name="input[phone]" 
                               placeholder="<?= _("Your phone"); ?>">
                    </div>
                    <div class="form-group<?php if (isset($errors['input.timezone'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputTimezone">
                            <?= _("Time Zone"); ?>:
                        </label>
                        <select name="input[timezone]" 
                                id="inputTimezone" 
                                class="form-control">
                            <option value="">
                                <?= _("Choose timezone"); ?>
                            </option>
                            <?php
                                foreach ($timezones as $key => $timezone):
                            ?>
                                    <option value="<?= $key; ?>"<?php if ((Input::post("input.timezone") !== null && Input::post("input.timezone") == $key) || (Input::post("input.timezone") === null && ($key == $user['timezone']))): echo ' selected="selected"'; endif; ?>>
                                        <?= Security::htmlentities($timezone); ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
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
