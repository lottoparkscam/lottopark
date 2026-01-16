<?php

use Models\Whitelabel;
use Fuel\Core\Input;
use Services\SocialMediaConnect\PresenterService;
use Helpers\{FlashMessageHelper,
    SocialMediaConnect\ConnectHelper,
    SocialMediaConnect\LastStepsHelper,
    SocialMediaConnect\ProfileHelper,
    UrlHelper,
    RouteHelper};

if (!defined('WPINC')) {
    die;
}
/**
 * We use sign up form on page-last-steps.php
 * (this page is created for sign up with social media. User can fill custom whitelabel data which we not download from social media).
 * Registration action only work on auth/signup (with other translations).
 * Flow: signup -> user press social connect button -> reidirect to page last steps
 * -> user fill form -> user press sign up button -> redirect to signup -> checking form -> session delete.
 */
$signUpUrl = '.';
$isLastSteps = LastStepsHelper::isLastStepsPage();
$isNotLastSteps = !$isLastSteps;

$shouldShowEmailInput = true;
$shouldShowNameInput = true;
$shouldShowSurnameInput = true;
$shouldShowPhoneInput = true;

if ($isLastSteps) {
    ConnectHelper::markRegisterAsSocialConnection();
    $signUpUrl = UrlHelper::getSignUpUrlInDefaultLanguage();
    $socialProfile = ProfileHelper::getSocialProfileFromSession();

    $emailNotExistsInSocialProfile = empty($socialProfile->email);
    $shouldShowEmailInput = $emailNotExistsInSocialProfile;

    $firstNameNotExistsInSocialProfile = empty($socialProfile->firstName);
    $shouldShowNameInput = $firstNameNotExistsInSocialProfile;

    $lastNameNotExistsInSocialProfile = empty($socialProfile->lastName);
    $shouldShowSurnameInput = $lastNameNotExistsInSocialProfile;

    $phoneNumberNotExistsInSocialProfile = empty($socialProfile->phone);
    $shouldShowPhoneInput = $phoneNumberNotExistsInSocialProfile;
} else {
    ConnectHelper::removeSocialConnectSession();
}
?>

<form class="platform-form platform-form-register" autocomplete="off" method="post" action="<?= $signUpUrl ?>">
    <input type="hidden" name="<?= \Config::get('security.csrf_token_key');?>"
           value="<?= \Security::fetch_token();?>" />
    <?php
        $lastErrors = FlashMessageHelper::getLast();
        $lastErrors = empty($lastErrors) ? [] : [$lastErrors];
        $this->errors = empty($lastErrors) ? $this->errors : $lastErrors;
        if (!empty($this->errors) && count($this->errors) > 0):
            FlashMessageHelper::remove();
    ?>
            <div class="platform-alert platform-alert-error">
                <?php
                    foreach ($this->errors as $error):
                        echo '<p><span class="fa fa-exclamation-circle"></span>' .
                        wp_kses(
                            $error,
                            array(
                                "a" => array(
                                    "href" => array(),
                                    "target" => array()
                                )
                            )
                        ) .
                        '</p>';
                    endforeach;
                ?>
            </div>
    <?php
        endif;
    ?>
    <p class="text-center">
        <?php
            $login_slug = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('login'));
            
            echo _("Already a member?");
        ?>
            <a href="<?= $login_slug; ?>"
               id="btn-login" 
               class="">
                <?= _("Login here"); ?>
            </a>
    </p>

    <?php
        $socialConnectPresenter = Container::get(PresenterService::class);
        echo $socialConnectPresenter->generateSocialButtonsView();

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $login_class_error = "";
        if (isset($this->errors['register.login'])) {
            $login_class_error = ' has-error';
        }

        $mail_class_error = "";
        if (isset($this->errors['register.email'])) {
            $mail_class_error = ' has-error';
        }
        
        $password_class_error = "";
        if (isset($this->errors['register.password'])) {
            $password_class_error = ' has-error';
        }
        
        $rpassword_class_error = "";
        if (isset($this->errors['register.rpassword'])) {
            $rpassword_class_error = ' has-error';
        }
        
        $currency_class_error = "";
        if (isset($this->errors['register.ucurrency'])) {
            $currency_class_error = ' has-error';
        }

        $name_class_error = "";
        if (isset($this->errors['register.name'])) {
            $name_class_error = ' has-error';
        }

        $surname_class_error = "";
        if (isset($this->errors['register.surname'])) {
            $surname_class_error = ' has-error';
        }

        $phone_class_error = "";
        if (isset($this->errors['register.phone']) || isset($this->errors['register.prefix'])) {
            $phone_class_error = ' has-error';
        }

        $company_class_error = "";
        if (isset($this->errors['register.company'])) {
            $company_class_error = ' has-error';
        }

        $promo_class_error = "";
        if (isset($this->errors['register.promo_code'])) {
            $promo_class_error = ' has-error';
        }

        $group_class_error = "";
        if (isset($this->errors['register.group'])) {
            $group_class_error = ' has-error';
        }
        
        $register_login_value = '';
        if (Input::post("register.login") !== null) {
            $register_login_value = htmlspecialchars(stripslashes(Input::post("register.login")));
        }

        $register_email_value = '';
        if (Input::post("register.email") !== null) {
            $register_email_value = htmlspecialchars(stripslashes(Input::post("register.email")));
        }

        $register_promo_code_value = '';
        if (Input::post("register.promo_code") !== null) {
            $register_promo_code_value = htmlspecialchars(stripslashes(Input::post("register.promo_code")));
        }

        $register_group_value = $whitelabel['default_whitelabel_user_group_id'];
        if (Input::post("register.group") !== null) {
            $register_group_value = htmlspecialchars(stripslashes(Input::post("register.group")));
        }

        $default_currencies_to_show = [];

        if (!empty($default_currencies)) {
            $final_currency_code = Helpers_Currency::get_final_currency_code();

            $currency_code_to_select = $final_currency_code;
            // In the case that error occured proper currency should be
            // selected instead of default
            if (Input::post("register.ucurrency") !== null) {
                $currency_id_to_find = (int)Input::post("register.ucurrency");
                $currency_tab_to_find = Helpers_Currency::get_mtab_currency(
                    false,
                    "",
                    $currency_id_to_find
                );

                if (!empty($currency_tab_to_find)) {
                    $currency_code_to_select = $currency_tab_to_find["code"];
                }
            }

            foreach ($default_currencies as $key => $default_currency) {
                $is_selected = "";
                if ((string)$currency_code_to_select === (string)$default_currency['currency_code']) {
                    $is_selected = ' selected="selected"';
                }

                $default_currencies_to_show[] = [
                    'currency_id' => $default_currency['currency_id'],
                    'currency_code' => $default_currency['currency_code'],
                    'is_selected' => $is_selected
                ];
            }
        }

        //currencies for honeypot
    ?>
    <input id="currency_a" name="currency_a" type="hidden" value="" />
    <input id="currency_b" name="currency_b" type="text" value="" />
    <input id="currency_c" name="currency_c" type="text" value="" tabindex="-1"  autocomplete="off"/>

    <?php
        /** @var Whitelabel $whitelabelModel */
        $whitelabelModel = Container::get('whitelabel');
        if ($whitelabelModel->loginForUserIsUsedDuringRegistration()):
    ?>
    <div class="form-group <?= $login_class_error; ?>">
        <input type="text" 
               autocomplete="login" 
               required 
               value="<?= $register_login_value; ?>" 
               class="form-control" 
               id="inputLogin" 
               name="register[login]" 
               placeholder="<?= htmlspecialchars(_("Your login")); ?>">
    </div>
    <?php
        endif;
        if ($shouldShowEmailInput):
    ?>
    <div class="form-group <?= $mail_class_error; ?>">
        <input type="email" 
               autocomplete="email" 
               required 
               value="<?= $register_email_value; ?>" 
               class="form-control" 
               id="inputEmail" 
               name="register[email]" 
               placeholder="<?= htmlspecialchars(_("Your e-mail address")); ?>">
    </div>
    <?php
        endif;
        /** On last steps page password is generated automatically */
        if ($isNotLastSteps):
    ?>
    <div class="form-group <?= $password_class_error; ?>">
        <input type="password" 
               required 
               autocomplete="new-password" 
               class="form-control" 
               id="inputPassword" 
               name="register[password]" 
               placeholder="<?= htmlspecialchars(_("Your password")); ?>">
    </div>
    
    <div class="form-group <?= $rpassword_class_error; ?>">
        <input type="password" 
               required 
               autocomplete="new-password" 
               class="form-control" 
               id="inputRPassword" 
               name="register[rpassword]" 
               placeholder="<?= htmlspecialchars(_("Repeat your password")); ?>">
    </div>
    <?php endif; ?>

    <div class="form group <?= $currency_class_error; ?>" >
        <select class="form-control" required name="register[ucurrency]">
            <option value="a" disabled="disabled">
                <?= htmlspecialchars(_("Choose account currency")); ?>
            </option>
            <?php
                if (!empty($default_currencies_to_show)):
                    foreach ($default_currencies_to_show as $key => $default_currency):
            ?>
                        <option value="<?= $default_currency['currency_id']; ?>" 
                                <?= $default_currency["is_selected"]; ?>>
                            <?= $default_currency['currency_code']; ?>
                        </option>
            <?php
                    endforeach;
                else:
                    $default_system_currency = Helpers_Currency::get_mtab_currency();
            ?>
                    <option value="<?= $default_system_currency['id']; ?>">
                        <?= $default_system_currency['code'] ?>
                    </option>
            <?php
                endif;
            ?>
        </select>
    </div>

    <?php
        if ($whitelabelModel->isNameAndSurnameUsedDuringRegistration()):
                if ($shouldShowNameInput):
        ?>
            <div class="form-group<?= $name_class_error; ?>">
                <input type="text"
                       autocomplete="name"
                       <?= $whitelabelModel->isNameSurnameRequiredDuringRegistration() ? 'required' : '' ?>
                       value="<?= Input::post("register.name") !== null ? htmlspecialchars(stripslashes(Input::post("register.name"))) : ''; ?>"
                       class="form-control"
                       id="inputName"
                       name="register[name]"
                       placeholder="<?= htmlspecialchars(_("First Name")); ?>">
            </div>

            <?php
                endif;
                if ($shouldShowSurnameInput):
            ?>
            <div class="form-group<?= $surname_class_error; ?>">
                <input type="text"
                       autocomplete="surname"
                       <?= $whitelabelModel->isNameSurnameRequiredDuringRegistration() ? 'required' : '' ?>
                       value="<?= Input::post("register.surname") !== null ? htmlspecialchars(stripslashes(Input::post("register.surname"))) : ''; ?>"
                       class="form-control"
                       id="inputSurname"
                       name="register[surname]"
                       placeholder="<?= htmlspecialchars(_("Last Name")); ?>">
            </div>
    <?php
        endif;
        endif;

        if ($whitelabelModel->isCompanyUsedDuringRegistration()):
            ?>
        <div class="form-group<?= $company_class_error; ?>">
            <input type="text"
                   autocomplete="company"
                <?= $whitelabelModel->isCompanyRequiredDuringRegistration() ? 'required' : '' ?>
                   value="<?= Input::post("register.company") !== null ? htmlspecialchars(stripslashes(Input::post("register.company"))) : ''; ?>"
                   class="form-control"
                   id="inputCompany"
                   name="register[company]"
                   placeholder="<?= htmlspecialchars(_("Charity | Company | Legal Entity")); ?>">
        </div>
    <?php
        endif;
        if ($whitelabelModel->isPhoneUsedDuringRegistration()):
            if ($shouldShowPhoneInput):
    ?>
            <div class="platform-form-profile-group">
                <div class="platform-form-profile-subgroup3">
                    <div class="form-group<?= $phone_class_error; ?>">
                        <select name="register[prefix]" id="inputPrefix"
                            <?= $whitelabelModel->isPhoneRequiredDuringRegistration() ? 'required' : '' ?>>
                            <option value="">
                                <?php echo Security::htmlentities(_("Choose country")); ?>
                            </option>
                            <?php
                            $countries = Lotto_Helper::get_localized_country_list();
                            $pcountries = Lotto_Helper::filter_phone_countries($countries);
                            $prefixes = Lotto_Helper::get_telephone_prefix_list();
                            foreach ($pcountries as $key => $country):
                                if (isset($prefixes[$key])):
                                    foreach ($prefixes[$key] as $item):
                                        ?>
                                        <option value="<?php echo htmlspecialchars($key); ?>_<?php echo htmlspecialchars($item); ?>"
                                                data-territory="<?php echo htmlspecialchars($key); ?>"<?php if (Input::post("register.prefix") !== null && stripslashes(Input::post("register.prefix")) == $key.'_'.$item): echo ' selected="selected"'; endif; ?>>
                                            <?php echo Security::htmlentities((isset($countries[$key]) ? $countries[$key]." (+".$item.")" : '+'.$item)); ?>
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
                    <div class="form-group<?php if (isset($this->errors['register.phone'])): echo ' has-error'; endif; ?>">
                        <input type="text"
                            <?= $whitelabelModel->isPhoneRequiredDuringRegistration() ? 'required' : '' ?>
                               value="<?php echo htmlspecialchars(Input::post("register.phone") !== null ? stripslashes(Input::post("register.phone")) : ''); ?>"
                                class="form-control"
                                id="inputPhone"
                                name="register[phone]"
                                placeholder="<?php echo Security::htmlentities(_("Phone")); ?>">
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
    <?php
        endif;
        endif;

        if ($bonus_active):
    ?>      
        <div class="form-promo-code <?= $promo_class_error; ?>">
            <label for="inputPromoCode"><?= Security::htmlentities(_("Got a promo code?")); ?></label>
            <input type="text"
                class="form-control" 
                id="inputPromoCode"                 
                value="<?= $register_promo_code_value; ?>" 
                name="register[promo_code]" 
                placeholder="<?= htmlspecialchars(_("Enter promo code")); ?>">                 
        </div>
    <?php
        endif;

        if (count($user_groups) > 0 && $whitelabel['can_user_select_group_while_register']):
    ?>
        <div class="form group <?= $currency_class_error; ?>" >
            <select class="form-control" name="register[group]">
                <option value="a" disabled="disabled">
                    <?= htmlspecialchars(_("Choose group")); ?>
                </option>
                <?php
                    foreach ($user_groups as $group):
                ?>
                        <option value="<?= $group["id"]; ?>" 
                                <?= $group["id"] === $register_group_value ? "selected" : ""; ?>>
                            <?= $group["name"]; ?>
                        </option>
                <?php
                    endforeach;
                ?>
            </select>
        </div>
    <?php
        endif;
        $termsSlug = IS_CASINO ? Lotto_Platform::CASINO_TERMS_SLUG : 'terms';
            $privacySlug = IS_CASINO ? Helper_Route::CASINO_PRIVACY_POLICY : 'privacy';
    ?>

    <div class="checkbox">
        <label>
            <input type="checkbox" name="register[accept]" required value="1"> <?php
                echo wp_kses(
        sprintf(
            _('I accept the <a href="%s" target="_blank">Terms</a> and the <a href="%s" target="_blank">Policy</a>'),
            RouteHelper::getPermalinkBySlug($termsSlug, $whitelabel['domain']),
            RouteHelper::getPermalinkBySlug($privacySlug, $whitelabel['domain'])
        ),
        [
            "a" => [
                "href" => [], 
                "target" => [], 
                "rel" => [],
            ]
        ]
    );
            ?>
        </label>
    </div>
    <?php
        if ($show_captcha) {
            echo \Helpers\CaptchaHelper::getCaptcha();
        }
    ?>
    <div class="platform-form-btn">
        <button type="submit" class="btn btn-primary btn-lg btn-mobile-large">
            <?= Security::htmlentities(_("Sign up")); ?>
        </button>
    </div>
    <div class="clearfix"></div>
</form>
