<?php

use Helpers\LogoHelper;

?>
<div class="container">
    <div class="aff-logo">
        <?= LogoHelper::getWhitelabelImgLogoSection() ?>
    </div>
    <form class="form-signup" autocomplete="off" method="post" action="/sign_up">
        <?php
            echo \Form::csrf();
        
            if (isset($this->errors)) {
                include(APPPATH . "views/aff/shared/errors.php");
            }
        ?>

        <div class="form-group">
            <h2 class="form-signup-heading"><?= _("Sign up"); ?></h2>
        </div>

        <div class="form-group <?= $error_classes["register_email"]; ?>">
            <label for="inputRegisterEmail" class="sr-only">
                <?= _("Email"); ?>
            </label>
            <input type="text" 
                   name="register[email]" 
                   id="inputRegisterEmail" 
                   class="form-control register-email" 
                   placeholder="<?= _("Email"); ?>" 
                   required 
                   autofocus
                   value="<?= $register_values["register_email"]; ?>">
        </div>

        <div class="form-group <?= $error_classes["register_login"]; ?>">
            <label for="inputRegisterLogin" class="sr-only">
                <?= _("Login"); ?>
            </label>
            <input type="text" 
                   name="register[login]" 
                   id="inputRegisterLogin" 
                   class="form-control register-login" 
                   placeholder="<?= _("Login"); ?>" 
                   required 
                   autofocus
                   value="<?= $register_values["register_login"]; ?>"
                   data-entered="<?= $register_values["register_login_entered"]; ?>">
        </div>

        <div class="form-group <?= $error_classes["register_password"]; ?>">
            <label for="inputRegisterPassword" class="sr-only">
                <?= _("Password"); ?>
            </label>
            <input type="password" 
                   name="register[password]" 
                   id="inputRegisterPassword" 
                   class="form-control register-password" 
                   placeholder="<?= _("Password"); ?>" 
                   required>
        </div>

        <div class="form-group <?= $error_classes["register_password_repeat"]; ?>">
            <label for="inputRegisterPasswordRepeat" class="sr-only">
                <?= _("Repeat password"); ?>
            </label>
            <input type="password" 
                   name="register[password_repeat]" 
                   id="inputRegisterPasswordRepeat" 
                   class="form-control register-password-repeat" 
                   placeholder="<?= _("Repeat password"); ?>" 
                   required>
        </div>

        <?php
            if (!Helpers_General::is_development_env()) {
                echo \Helpers\CaptchaHelper::getCaptcha();
            }
        ?>

        <button class="btn btn-lg btn-primary btn-block" 
                type="submit" 
                id="submitAffRegister">
            <?= _("Sign up"); ?>
        </button>
    </form>
    
    <div>
        <p class="text-center">
            <?= _("Already a member?"); ?>
            <b><a href="<?= $sign_in_link; ?>"><?= _("Login here"); ?></a></b>
        </p>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="registerProcessing" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <p><?= _("Please wait...processing"); ?></p>
            </div>

        </div>
    </div>
</div>