<?php
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php
            include(APPPATH . "views/whitelabel/affs/menu.php");
        ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Edit details"); ?> <small><?= Security::htmlentities($user['email']); ?></small>
        </h2>
		<p class="help-block">
            <?= _("Edit affiliate details here."); ?>
        </p>
		<a href="/affs/list/view/<?= $user['token']; ?><?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
			<div class="container-fluid container-admin row">
                <div class="col-md-6">
                    <form method="post" action="/affs/list/edit/<?= $user['token']; ?><?= Lotto_View::query_vars(); ?>">
                        <?php
                            if (isset($this->errors)) {
                                include(APPPATH . "views/whitelabel/shared/errors.php");
                            }
                        
                            $company = '';
                            if (Input::post("input.company") !== null):
                                $company = Input::post("input.company");
                            elseif (!empty($user['company'])):
                                $company = $user['company'];
                            endif;

                            $name = '';
                            if (Input::post("input.name") !== null):
                                $name = Input::post("input.name");
                            elseif (!empty($user['name'])):
                                $name = $user['name'];
                            endif;

                            $surname = '';
                            if (Input::post("input.surname") !== null):
                                $surname = Input::post("input.surname");
                            elseif (!empty($user['surname'])):
                                $surname = $user['surname'];
                            endif;

                            $city = '';
                            if (Input::post("input.city") !== null):
                                $city = Input::post("input.city");
                            elseif (!empty($user['city'])):
                                $city = $user['city'];
                            endif;

                            $zip = '';
                            if (Input::post("input.zip") !== null):
                                $zip = Input::post("input.zip");
                            elseif (!empty($user['zip'])):
                                $zip = $user['zip'];
                            endif;

                            $address = '';
                            if (Input::post("input.address") !== null):
                                $address = Input::post("input.address");
                            elseif (!empty($user['address_1'])):
                                $address = $user['address_1'];
                            endif;

                            $address2 = '';
                            if (Input::post("input.address_2") !== null):
                                $address2 = Input::post("input.address_2");
                            elseif (!empty($user['address_2'])):
                                $address2 = $user['address_2'];
                            endif;

                            $birthdate = '';
                            if (Input::post("input.birthdate") !== null):
                                $birthdate = Input::post("input.birthdate");
                            else:
                                if (!empty($user['birthdate'])):
                                    $birthdate_tmp = new DateTime($user['birthdate'], new DateTimeZone("UTC"));
                                    $birthdate = $birthdate_tmp->format('m/d/Y');
                                endif;
                            endif;

                            $phone = '';
                            if (Input::post("input.phone") !== null):
                                $phone = Input::post("input.phone");
                            else:
                                if (!empty($user['phone']) && !empty($user['phone_country'])):
                                    $phone = Lotto_View::format_phone($user['phone'], $user['phone_country'], true);
                                endif;
                            endif;
                        ?>
                        
                        <div class="form-group<?php if (isset($errors['input.lotteryGroup'])): echo ' has-error';
                        endif; ?>">
                            <label class="control-label" for="inputLotteryGroup">
                                Lottery Group
                            </label>
                            <select name="input[lotteryGroup]"
                                    id="inputLotteryGroup"
                                    class="form-control">
                                <option value="0">
                                    Default Lottery Group
                                </option>
                                <?php
                                foreach ($lotteryGroups as $lotteryGroup):
                                    $isSelected = $user['whitelabel_aff_group_id'] == $lotteryGroup['id'];
                                ?>
                                    <option
                                      value="<?= $lotteryGroup['id'] ?>"
                                        <?= $isSelected ? 'selected="selected"' : '' ?>>
                                        <?= $lotteryGroup['name'] ?>
                                    </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <div class="form-group<?php if (isset($errors['input.casinoGroup'])): echo ' has-error';
                        endif; ?>">
                            <label class="control-label" for="inputCasinoGroup">
                                Casino Group
                            </label>
                            <select name="input[casinoGroup]"
                                    id="inputCasinoGroup"
                                    class="form-control">
                                <option value="0">
                                    Default Casino Group
                                </option>
                                <?php foreach ($casinoGroups as $casinoGroup): ?>
                                    <option
                                      value="<?= $casinoGroup['id'] ?>"
                                        <?= $user['whitelabel_aff_casino_group_id'] == $casinoGroup['id'] ? 'selected="selected"' : '' ?>>
                                        <?= $casinoGroup['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group <?php if (isset($errors['input.parentToken'])): echo ' has-error'; endif; ?>">
                          <label class="control-label" for="inputParentToken">
                            Parent Aff Token:
                            <?php if(!empty($affiliateParentDetails['token'])): ?>
                              <a href="/affs/list/view/<?= $affiliateParentDetails['token']?>" class="btn btn-xs btn-default">Parent Profile</a>
                            <?php endif; ?>
                          </label>
                          <input type="text" class="form-control" id="inputParentToken" name="input[parentToken]" placeholder="Provide parent token..." value="<?= $affiliateParentDetails['token'] ?>" />
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
                                   placeholder="<?= _("User company"); ?>">
                        </div>
                        
                        <div class="form-group<?php if (isset($errors['input.login'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputLogin">
                                <?= _("Login"); ?>:
                            </label>
                            <input type="text" 
                                   value="<?= Security::htmlentities(null !== Input::post("input.login") ? Input::post("input.login") : $user['login']); ?>" 
                                   class="form-control" 
                                   id="inputLogin" 
                                   name="input[login]" 
                                   placeholder="<?= _("Enter login"); ?>">
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
                                   placeholder="<?= _("User first name"); ?>">
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
                                   placeholder="<?= _("User last name"); ?>">
                        </div>
                        
                        <div class="form-group<?php if (isset($errors['input.country'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputCountry">
                                <?= _("Country"); ?>:
                            </label>
                            <select name="input[country]" 
                                    id="inputCountry" 
                                    class="form-control">
                                <option value="">
                                    <?= _("Choose user country"); ?>
                                </option>
                                <?php
                                    foreach ($countries as $key => $country):
                                        $option_selected = '';
                                        if ((Input::post("input.country") !== null &&
                                                Input::post("input.country") == $key) ||
                                            (Input::post("input.country") === null &&
                                                $key == $user['country'])
                                        ) {
                                            $option_selected = ' selected="selected"';
                                        }
                                ?>
                                        <option value="<?= $key; ?>"<?= $option_selected; ?>>
                                            <?= Security::htmlentities($country); ?>
                                        </option>
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
                                   placeholder="<?= _("User city"); ?>">
                        </div>
                        
                        <?php
                            $state_class_error = '';
                            if (isset($errors['input.state'])) {
                                $state_class_error = ' has-error';
                            }
                            
                            $state_disabled = '';
                            $country_value = '';
                            if (Input::post("input.country") !== null) {
                                $country_value = Input::post("input.country");
                            } else {
                                $country_value = $user['country'];
                            }
                            if (!Lotto_View::regions_exist($country_value)) {
                                $state_disabled = ' disabled';
                            }
                        ?>
                        <div class="form-group<?= $state_class_error; ?>">
                            <label class="control-label" for="inputState">
                                <?= _("Region"); ?>:
                            </label>
                            <select name="input[state]" 
                                    id="inputState" 
                                    class="form-control"<?= $state_disabled; ?>>
                                <option value="">
                                    <?= _("Choose user region"); ?>
                                </option>
                                <?php Lotto_View::generate_region_list($user['state'], $country_value); ?>
                            </select>
                            <select id="allRegions">
                                <option value="">
                                    <?= _("Choose user region"); ?>
                                </option>
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
                                   placeholder="<?= _("User Postal/ZIP Code"); ?>">
                        </div>
                        
                        <div class="form-group<?php if (isset($errors['input.address'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputAddress">
                                <?= _("Address #1"); ?>:
                            </label>
                            <input type="text" 
                                   value="<?= Security::htmlentities($address); ?>" 
                                   class="form-control" 
                                   id="inputAddress" 
                                   name="input[address]" 
                                   placeholder="<?= _("User address #1"); ?>">
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
                                   placeholder="<?= _("User address #2"); ?>">
                        </div>
                        
                        <div class="form-group<?php if (isset($errors['input.birthdate'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputBirthdate">
                                <?= _("Birthdate"); ?>:
                            </label>
                            <input type="text" 
                                   value="<?= Security::htmlentities($birthdate); ?>" 
                                   class="form-control datepicker" 
                                   id="inputBirthdate" 
                                   name="input[birthdate]" 
                                   placeholder="<?= _("User birthdate"); ?>" 
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
                        <div class="form-group<?php if (isset($errors['input.prefix'])): echo ' has-error'; endif; ?>">
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
                        
                        <div class="form-group<?php if (isset($errors['input.phone'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputPhone">
                                <?= _("Phone"); ?>:
                            </label>
                            <input type="text" 
                                   value="<?= Security::htmlentities($phone); ?>" 
                                   class="form-control" 
                                   id="inputPhone" 
                                   name="input[phone]" 
                                   placeholder="<?= _("User phone"); ?>">
                        </div>
                        
                        <div class="form-group<?php if (isset($errors['input.timezone'])): echo ' has-error'; endif; ?>">
                            <label class="control-label" for="inputTimezone">
                                <?= _("Time Zone"); ?>:
                            </label>
                            <select name="input[timezone]" id="inputTimezone" class="form-control">
                                <option value="">
                                    <?= _("Choose time zone"); ?>
                                </option>
                                <?php
                                    foreach ($timezones as $key => $timezone):
                                        $option_selected = '';
                                        if ((Input::post("input.timezone") !== null &&
                                                Input::post("input.timezone") == $key) ||
                                            (Input::post("input.timezone") === null &&
                                                ($key == $user['timezone']))
                                        ) {
                                            $option_selected =' selected="selected"';
                                        }
                                ?>
                                        <option value="<?= $key; ?>"<?= $option_selected; ?>>
                                            <?= Security::htmlentities($timezone); ?>
                                        </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>

                        <?php include(APPPATH."views/whitelabel/shared/affs/edit.php"); ?>
                        
                        <button type="submit" class="btn btn-primary">
                            <?= _("Submit"); ?>
                        </button>
                    </form>
                </div>
            </div>
		</div>
	</div>
</div>
