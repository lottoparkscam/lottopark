<?php

use Helpers\UrlHelper;
use Models\Whitelabel;

if (!defined('WPINC')) {
    die;
}

?>
<form class="platform-form platform-form-lostpassword" autocomplete="off" method="post" action=".">
    <?php
        echo \Form::csrf();
        
        if (!empty(Input::post("lost")) &&
            !empty($this->errors) &&
            count($this->errors) > 0
        ):
    ?>
            <div class="platform-alert platform-alert-error">
                <?php
                    foreach ($this->errors as $error):
                        echo '<p><span class="fa fa-exclamation-circle"></span> ' . Security::htmlentities($error) . '</p>';
                    endforeach;
                ?>
            </div>
    <?php
        endif;
        
        $login_slug = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('login'));
    ?>
        <p class="text-center">
            <?= _("Remember your password?"); ?>
            <a href="<?= $login_slug; ?>"
               id="btn-login" 
               class="">
                <?= _("Login here"); ?>
            </a>
        </p>
    <?php
        if (isset($this->step) && $this->step == 2):
            $password_error_class = '';
            if (isset($this->errors['lost.password'])) {
                $password_error_class = ' has-error';
            }
            
            $repeat_password_error_class = '';
            if (isset($this->errors['lost.rpassword'])) {
                $repeat_password_error_class = ' has-error';
            }
    ?>
            <input type="hidden" value="1" name="lost[newpassword]">
            
            <div class="form-group <?= $password_error_class; ?>">
                <input type="password" 
                       required 
                       class="form-control" 
                       id="inputLostPassword" 
                       name="lost[password]" 
                       placeholder="<?= htmlspecialchars(_("Your new password")); ?>">
            </div>
            
            <div class="form-group <?= $repeat_password_error_class; ?>">
                <input type="password" 
                       required 
                       class="form-control" 
                       id="inputRPassword" 
                       name="lost[rpassword]" 
                       placeholder="<?= htmlspecialchars(_("Repeat your new password")); ?>">
            </div>
            
            <div class="platform-form-btn">
                <button type="submit" class="btn btn-primary btn-lg">
                    <?= Security::htmlentities(_("Change")); ?>
                </button>
            </div>
    <?php
        else:
            $email_error_class = '';
            if (isset($this->errors['lost.email'])) {
                $email_error_class = ' has-error';
            }
            
            $email_value = '';
            if (Input::post("lost.email") !== null) {
                $email_value = htmlspecialchars(stripslashes(Input::post("lost.email")));
            }
            $login_error_class = '';
            if (isset($this->errors['lost.login'])) {
                $login_error_class = ' has-error';
            }
            
            $login_value = '';
            if (Input::post("lost.login") !== null) {
                $login_value = htmlspecialchars(stripslashes(Input::post("lost.login")));
            }
    ?>
            <input type="hidden" value="1" name="lost[request]">
            
    <?php
            /** @var Whitelabel $whitelabel */
            $whitelabel = Container::get('whitelabel');
        if ($whitelabel->loginForUserIsUsedDuringRegistration()):
    ?>
            <div class="form-group <?= $login_error_class; ?>">
                <input type="text" 
                       required 
                       value="<?= $login_value; ?>" 
                       class="form-control" 
                       id="inputLostLogin" 
                       name="lost[login]" 
                       placeholder="<?= htmlspecialchars(_("Your login")); ?>">
            </div>
    <?php
        else:
    ?>
            <div class="form-group <?= $email_error_class; ?>">
                <input type="email" 
                       required 
                       value="<?= $email_value; ?>" 
                       class="form-control" 
                       id="inputLostEmail" 
                       name="lost[email]" 
                       placeholder="<?= htmlspecialchars(_("Your e-mail address")); ?>">
            </div>
    <?php
        endif;

        echo \Helpers\CaptchaHelper::getCaptcha();
    ?>
            
            <div class="platform-form-btn">
                <button type="submit" class="btn btn-primary btn-lg btn-mobile-large">
                    <?= Security::htmlentities(_("Send")); ?>
                </button>
            </div>
    <?php
        endif;
    ?>
    <div class="clearfix"></div>
</form>
