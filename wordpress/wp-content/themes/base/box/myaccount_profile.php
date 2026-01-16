<form class="platform-form platform-form-profile" autocomplete="off" method="post" action=".">
    <?php
        echo \Form::csrf();
        
        if (!empty($this->errors) && count($this->errors) > 0):
    ?>
            <div class="platform-alert platform-alert-error">
                <?php
                    foreach ($this->errors as $error):
                        echo '<p><span class="fa fa-exclamation-circle"></span> '.Security::htmlentities($error).'</p>';
                    endforeach;
                ?>
            </div>
    <?php
        endif;

        echo lotto_platform_messages();
    ?>
    <div class="platform-form-profile-side-left">
        <div class="platform-form-profile-group">
            <div class="form-group<?php if (isset($this->errors['profile.name'])): echo ' has-error'; endif; ?>">
                <label for="inputName">
                    <?= Security::htmlentities(_("First Name")); ?>:
                </label>
                <input type="text"<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                       value="<?= htmlspecialchars(Input::post("profile.name") !== null ? stripslashes(Input::post("profile.name")) : $user['name']); ?>" 
                       class="form-control" 
                       id="inputName" 
                       name="profile[name]">
            </div>
        </div>
        
        <div class="platform-form-profile-group">
            <div class="form-group<?php if (isset($this->errors['profile.surname'])): echo ' has-error'; endif; ?>">
                <label for="inputSurname">
                    <?= Security::htmlentities(_("Last Name")); ?>:
                </label>
                <input type="text"<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                       value="<?= htmlspecialchars(Input::post("profile.surname") !== null ? stripslashes(Input::post("profile.surname")) : $user['surname']); ?>" 
                       class="form-control" 
                       id="inputSurname" 
                       name="profile[surname]">
            </div>
        </div>
        
        <div class="platform-form-profile-group">
            <div class="form-group<?php if (isset($this->errors['profile.birthdate'])): echo ' has-error'; endif; ?>">
                <label for="inputBirthdate">
                    <?= Security::htmlentities(_("Birthdate")); ?>:
                </label>
                <input type="text"<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                       value="<?= htmlspecialchars(Input::post("profile.birthdate") !== null ? stripslashes(Input::post("profile.birthdate")) : (!empty($user['birthdate']) ? Lotto_View::format_date_without_timezone($user['birthdate'], IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE) : '')); ?>" 
                       class="form-control date-control" 
                       id="inputBirthdate" 
                       name="profile[birthdate]" 
                       data-dateformat="<?= Lotto_View::get_date_pattern(IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE); ?>">
                <input type="hidden" 
                       value="<?= htmlspecialchars(Input::post("profile.birthdate") !== null ? stripslashes(Input::post("profile.birthdate_post")) : $user['birthdate']); ?>" 
                       id="inputBirthdatePost" 
                       name="profile[birthdate_post]">
            </div>
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
        <div class="platform-form-profile-group">
            <label for="inputPhone">
                <?= Security::htmlentities(_("Phone")); ?>:
            </label>
            <div class="platform-form-profile-subgroup3">
                <div class="form-group<?php if (isset($this->errors['profile.prefix'])): echo ' has-error'; endif; ?>">
                    <select<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                        name="profile[prefix]" 
                        id="inputPrefix">
                        <option value="">
                            <?php if ($section == 'profile'): echo Security::htmlentities(_("Choose country")); endif; ?>
                        </option>
                        <?php
                            foreach ($pcountries as $key => $country):
                                if (isset($prefixes[$key])):
                                    foreach ($prefixes[$key] as $item):
                                        $value_phone_prefix = htmlspecialchars($key);
                                        $value_phone_prefix .= "_";
                                        $value_phone_prefix .= htmlspecialchars($item);
                                        
                                        $territory_phone_prefix = htmlspecialchars($key);
                                        
                                        $option_selected = '';
                                        if ((Input::post("input.prefix") !== null &&
                                                stripslashes(Input::post("input.prefix")) == $value_phone_prefix) ||
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
            </div>
            <div class="platform-form-profile-subgroup4">
                <div class="form-group<?php if (isset($this->errors['profile.phone'])): echo ' has-error'; endif; ?>">
                    <input<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                        type="text" 
                        value="<?= htmlspecialchars(Input::post("profile.phone") !== null ? stripslashes(Input::post("profile.phone")) : Lotto_View::format_phone($user['phone'], $user['phone_country'], true)); ?>" 
                        class="form-control" 
                        id="inputPhone" 
                        name="profile[phone]">
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        
        <div class="platform-form-profile-group">
            <div class="form-group<?php if (isset($this->errors['profile.timezone'])): echo ' has-error'; endif; ?>">
                <label for="inputTimezone">
                    <?= Security::htmlentities(_("Time Zone")); ?>:
                </label>
                <select<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                    name="profile[timezone]" 
                    id="inputTimezone">
                    <option value="">
                        <?php if ($section == 'profile'): echo Security::htmlentities(_("Choose time zone")); endif; ?>
                    </option>
                    <?php
                        foreach ($timezones as $key => $timezone):
                    ?>
                            <option value="<?= htmlspecialchars($key); ?>"<?php if ((Input::post("profile.timezone") !== null && stripslashes(Input::post("profile.timezone")) == $key) || (Input::post("profile.timezone") === null && ($key == $user['timezone']))): echo ' selected="selected"'; endif; ?>>
                                <?= Security::htmlentities($timezone); ?>
                            </option>
                    <?php
                        endforeach;
                    ?>
                </select>
            </div>
        </div>
        
        <div class="platform-form-profile-group">
            <div class="form-group<?php if (isset($this->errors['profile.gender'])): echo ' has-error'; endif; ?>">
                <label for="inputGender">
                    <?= Security::htmlentities(_("Gender")); ?>:
                </label>
                <select<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                    name="profile[gender]" 
                    id="inputGender">
                    <?php
                        $genders = Helpers_Wordpress_Resources::get_genders($section === 'profile');
                        foreach ($genders as $key => $gender):
                    ?>
                            <option value="<?= htmlspecialchars($key); ?>"<?php if ((Input::post("profile.gender") !== null && stripslashes(Input::post("profile.gender")) == $key) || (Input::post("profile.gender") === null && $key == $user['gender'])): echo ' selected="selected"'; endif; ?>>
                                <?= Security::htmlentities($gender); ?>
                            </option>
                    <?php
                        endforeach;
                    ?>
                </select>
            </div>
        </div>
        
    </div>
    
    <div class="platform-form-profile-side-right">
        <div class="platform-form-profile-group">
            <div class="form-group<?php if (isset($this->errors['profile.country'])): echo ' has-error'; endif; ?>">
                <label for="inputCountry">
                    <?= Security::htmlentities(_("Country")); ?>:
                </label>
                <select<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                    name="profile[country]" 
                    id="inputCountry">
                    <option value="">
                        <?php if ($section == 'profile'): echo Security::htmlentities(_("Choose your country")); endif; ?>
                    </option>
                    <?php
                        foreach ($countries as $key => $country):
                    ?>
                            <option value="<?= htmlspecialchars($key); ?>"<?php if ((Input::post("profile.country") !== null && stripslashes(Input::post("profile.country")) == $key) || (Input::post("profile.country") === null && $key == $user['country'])): echo ' selected="selected"'; endif; ?>>
                                <?= Security::htmlentities($country); ?>
                            </option>
                    <?php
                        endforeach;
                    ?>
                </select>
            </div>
        </div>
        
        <div class="platform-form-profile-group">
            <div class="form-group<?php if (isset($this->errors['profile.state'])): echo ' has-error'; endif; ?>">
                <label for="inputState">
                    <?= Security::htmlentities(_("Region")); ?>:
                </label>
                <select<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                    name="profile[state]" 
                    id="inputState"<?php if (!Lotto_View::regions_exist(Input::post("profile.country") !== null ? Input::post("profile.country") : $user['country'])): echo ' disabled'; endif; ?>>
                    <option value="">
                        <?php if ($section == 'profile'): echo Security::htmlentities(_("Choose region")); endif; ?>
                    </option>
                    <?php
                        Lotto_View::generate_region_list($user['state'], Input::post("profile.country") !== null ? Input::post("profile.country") : $user['country']);
                    ?>
                </select>
                <select<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                    id="allRegions">
                    <option value="">
                        <?php if ($section == 'profile'): echo Security::htmlentities(_("Choose region")); endif; ?>
                    </option>
                    <?php
                        Lotto_View::generate_region_list($user['state'], null, true);
                    ?>
                </select>
            </div>
        </div>
        
        <div class="platform-form-profile-group">
            <div class="platform-form-profile-subgroup">
                <div class="form-group<?php if (isset($this->errors['profile.city'])): echo ' has-error'; endif; ?>">
                    <label for="inputCity">
                        <?= Security::htmlentities(_("City")); ?>:
                    </label>
                    <input<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                        type="text" 
                        value="<?= htmlspecialchars(Input::post("profile.city") !== null ? stripslashes(Input::post("profile.city")) : $user['city']); ?>" 
                        class="form-control" 
                        id="inputCity" 
                        name="profile[city]">
                </div>
            </div>
            <div class="platform-form-profile-subgroup2">
                <div class="form-group<?php if (isset($this->errors['profile.zip'])): echo ' has-error'; endif; ?>">
                    <label for="inputZIP">
                        <?= Security::htmlentities(_("Postal/ZIP Code")); ?>:
                    </label>
                    <input<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                        type="text" 
                        value="<?= htmlspecialchars(Input::post("profile.zip") !== null ? stripslashes(Input::post("profile.zip")) : $user['zip']); ?>" 
                        class="form-control" 
                        id="inputZIP" 
                        name="profile[zip]">
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        
        <div class="platform-form-profile-group">
            <div class="form-group<?php if (isset($this->errors['profile.address'])): echo ' has-error'; endif; ?>">
                <label for="inputAddress">
                    <?= Security::htmlentities(_("Address")); ?>:
                </label>
                <input<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                    type="text" 
                    value="<?= htmlspecialchars(Input::post("profile.address") !== null ? stripslashes(Input::post("profile.address")) : $user['address_1']); ?>"
                    class="form-control" 
                    id="inputAddress" 
                    name="profile[address]">
            </div>
        </div>
        
        <div class="platform-form-profile-group">
            <div class="form-group<?php if (isset($this->errors['profile.address_2'])): echo ' has-error'; endif; ?>">
                <label for="inputAddress2">
                    <?= Security::htmlentities(_("Address (optional additional information)")); ?>:
                </label>
                <input<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                    type="text" 
                    value="<?= htmlspecialchars(Input::post("profile.address_2") !== null ? stripslashes(Input::post("profile.address_2")) : $user['address_2']); ?>"
                    class="form-control" 
                    id="inputAddress2" 
                    name="profile[address_2]">
            </div>
        </div>
        <div class="platform-form-profile-group">
            <div class="form-group<?php if (isset($this->errors['profile.national_id'])): echo ' has-error'; endif; ?>">
                <label for="inputNationalId">
                    <?= Security::htmlentities(_("National id")); ?>:
                </label>
                <input<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                    type="text" 
                    value="<?= htmlspecialchars(Input::post("profile.national_id") !== null ? stripslashes(Input::post("profile.national_id")) : $user['national_id']); ?>" 
                    class="form-control" 
                    id="inputNationalId" 
                    name="profile[national_id]">
            </div>
        </div>
    </div>
    
    <div class="clearfix"></div>
    <?php
        $selected_group = null;
        if (isset($user['prize_payout_whitelabel_user_group_id'])) {
            $selected_group = $user['prize_payout_whitelabel_user_group_id'];
        }
        if (Input::post("profile.group")) {
            $selected_group = Input::post("profile.group");
        }
        if ((int)$whitelabel['user_can_change_group'] === 1): ?>
        <hr>
        <div class="platform-form-profile-side-left">
            <div class="form-group<?php if (isset($this->errors['profile.group'])): echo ' has-error'; endif; ?>">
                <label for="inputGroup">
                    <?= Security::htmlentities(_("Prize payout group")); ?>:
                </label>
                <select<?php if ($section != 'profile'): echo ' disabled="disabled"'; endif; ?> 
                    name="profile[group]" 
                    id="inputGroup">
                    <option value="" disabled="disabled" <?php if (empty($selected_group)): echo ' selected="selected"'; endif; ?>>
                        <?php if ($section == 'profile'): echo Security::htmlentities(_("Choose group")); endif; ?>
                    </option>
                    <?php
                    foreach ($groups as $id => $group):
                        if (((int)$group['is_selectable_by_user'] === 1) || ($id == $user['prize_payout_whitelabel_user_group_id']) || $id == $whitelabel['default_whitelabel_user_group_id']):
                    ?>
                            <option value="<?= $id; ?>"<?php if ($selected_group == $id): echo ' selected="selected"'; endif; ?>>
                                <?= $group["name"]; ?>
                            </option>
                    <?php
                        endif;
                     endforeach;
                    ?>
                </select>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php endif; ?>
    
    <hr>
    <?php
        if ($section != 'profile'):
    ?>
            <div class="platform-form-profile-side-left">
                <div class="platform-form-profile-group">
                    <div class="form-group<?php if (isset($this->errors['profile.email'])): echo ' has-error'; endif; ?>">
                        <label for="inputEmail">
                            <?= Security::htmlentities(_("E-mail")); ?>:
                        </label>
                        <input disabled="disabled" 
                               type="email" 
                               required 
                               value="<?= htmlspecialchars(Input::post("profile.email") !== null ? stripslashes(Input::post("profile.email")) : $user['email']); ?>" 
                               class="form-control" 
                               id="inputEmail" 
                               name="profile[email]">
                    </div>
                </div>
            </div>
    <?php
        else:
    ?>
            <?php if (!$isSocialConnected): ?>
                <div class="platform-form-profile-side-left">
                    <a href="#"
                       class="lotto-toggle myaccount-toggle-profile"
                       data-togglename="<?php
                       echo empty(Input::post("profile.remail"))
                           ? _( "Click here to cancel the e-mail change")
                           : _( "Change e-mail");
                       ?>">
                        <?php
                        echo empty(Input::post("profile.remail"))
                            ? _( "Change e-mail")
                            : _( "Click here to cancel the e-mail change");
                        ?>
                    </a>

                    <div class="platform-form-profile-group<?php
                    if (!isset($this->errors['profile.email']) &&
                        (empty(Input::post("profile.email")) || $user['email'] == Input::post("profile.email"))
                    ):
                        echo ' hidden-normal';
                    endif;
                    ?>">
                        <div class="platform-form-profile-group">
                            <label for="currentPassword">
                                <?= Security::htmlentities(_("Current password")); ?>:
                            </label>
                            <input type="password"
                                   value=""
                                   class="form-control"
                                   id="currentPassword"
                                   name="profile[current_password]"
                                   autocomplete="new-password">
                        </div>
                        <div class="platform-form-profile-subgroup3">
                            <div class="form-group<?php if (isset($this->errors['profile.email'])): echo ' has-error'; endif; ?>">
                                <label for="inputEmail">
                                    <?= _("E-mail") ?>:
                                </label>
                                <input type="email"
                                       value="<?= htmlspecialchars(Input::post("profile.email") !== null ? stripslashes(Input::post("profile.email")) : $user['email']); ?>"
                                       class="form-control"
                                       id="inputEmail"
                                       name="profile[email]">
                            </div>
                        </div>

                        <div class="platform-form-profile-subgroup4">
                            <label for="inputREmail">
                                <?= _("Repeat e-mail") ?>:
                            </label>
                            <input type="email"
                                   value="<?= htmlspecialchars(Input::post("profile.remail") !== null ? stripslashes(Input::post("profile.remail")) : ''); ?>"
                                   class="form-control"
                                   id="inputREmail"
                                   name="profile[remail]">
                        </div>

                        <div class="clearfix"></div>

                        <p class="help-block">
                            <?= _("You will need to activate your new e-mail address.") ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            <div class="platform-form-profile-side-right">
                <a href="#" 
                   class="lotto-toggle myaccount-toggle-profile" 
                   data-togglename="<?php
                        if (empty(Input::post("profile.password"))):
                            echo Security::htmlentities(_("Click here to cancel the password change"));
                        else:
                            echo Security::htmlentities(_("Change password"));
                        endif;
                        ?>">
                        <?php
                            if (empty(Input::post("profile.password"))):
                                echo Security::htmlentities(_("Change password"));
                            else:
                                echo Security::htmlentities(_("Click here to cancel the password change"));
                            endif;
                        ?>
                </a>

                <div class="platform-form-profile-group<?php if (empty(Input::post("profile.password"))): echo ' hidden-normal'; endif; ?>">
                    <div class="platform-form-profile-subgroup5">
                        <label for="inputCurrentPassword">
                            <?= Security::htmlentities(_("Old password")); ?>:
                        </label>
                        <input type="password" 
                               value="" 
                               class="form-control" 
                               id="inputCurrentPassword" 
                               name="profile[password]"
                               autocomplete="new-password">
                    </div>
                    <div class="platform-form-profile-subgroup5">
                        <label for="inputNewPassword">
                            <?= Security::htmlentities(_("New password")); ?>:
                        </label>
                        <input type="password" 
                               value="" 
                               class="form-control" 
                               id="inputNewPassword" 
                               name="profile[npassword]"
                               autocomplete="new-password">
                    </div>
                    <div class="platform-form-profile-subgroup5">
                        <label for="inputRNewPassword">
                            <?= Security::htmlentities(_("Repeat password")); ?>:
                        </label>
                        <input type="password" 
                               value="" 
                               class="form-control" 
                               id="inputRNewPassword" 
                               name="profile[rpassword]"
                               autocomplete="new-password">
                    </div>
                </div>
            </div>
    <?php
        endif;
    ?>
    
    <div class="clearfix"></div>
    
    <?php
        if ($section == "profile"):
    ?>
            <div class="text-right">
                <button type="submit" 
                        class="btn btn-primary btn-lg platform-form-button-margin btn-mobile-large">
                    <?= Security::htmlentities(_("Save changes")); ?>
                </button>
            </div>
    <?php
        else:
    ?>
            <br>
            <div class="platform-form-profile-side-left">
                &nbsp;
            </div>
            <div class="platform-form-profile-side-right">
                <div class="platform-form-profile-group myaccount-personal-group">
                    <div class="myaccount-personal-link">
                        <a href="<?= lotto_platform_get_permalink_by_slug('account'); ?>exportalldata/" 
                           class="user-personal-data">
                            <?= Security::htmlentities(_("Download personal data")); ?>
                        </a>
                    </div>
                    <div class="myaccount-personal-link">
                        <a href="#" 
                           class="delete-password " 
                           id="myaccount-remove-link">
                            <?= Security::htmlentities(_("Delete account")); ?>
                        </a>
                    </div>
                </div>
            </div>
    <?php
        endif;
    ?>
    
    <div class="clearfix"></div>
</form>

