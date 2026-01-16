<?php

use Helpers\LogoHelper;

?>
<div class="container">
    <div class="aff-logo">
        <?= LogoHelper::getWhitelabelImgLogoSection() ?>
    </div>
    <form class="form-signin" method="post" action=".">
        <?php
        echo \Form::csrf();

            if ((int)$whitelabel["aff_enable_sign_ups"] === 1 &&
                null !== Session::get_flash("resend_link")
            ):
                $resend_link = Session::get_flash("resend_link");
                $resend_message_start = Session::get_flash("resend_message_start");
                $resend_message_middle = Session::get_flash("resend_message_middle");
                $resend_message_end = Session::get_flash("resend_message_end");
        ?>
                <div class="alert alert-warning" role="alert">
                    <button type="button"
                            class="close"
                            data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    <p>
                        <?= $resend_message_start; ?>
                        <b>
                            <a href="<?= $resend_link; ?>">
                                <?= $resend_message_middle; ?>
                            </a>
                        </b>
                        <?= $resend_message_end; ?>
                    </p>
                </div>
        <?php
            endif;

            include(APPPATH . "views/aff/shared/messages.php");

            if (isset($this->errors)) {
                include(APPPATH . "views/aff/shared/errors.php");
            }
        ?>
		<h2 class="form-signin-heading"><?= _("Please sign in"); ?></h2>

		<div class="form-group <?= $error_classes["name"]; ?>">
			<label for="inputLogin" class="sr-only">
                <?= _("Login"); ?>
            </label>
			<input type="text"
                   name="login[name]"
                   id="inputLogin"
                   class="form-control"
                   placeholder="<?= _("Login/E-mail"); ?>"
                   required autofocus>
		</div>

		<div class="form-group <?= $error_classes["password"]; ?>">
			<label for="inputPassword" class="sr-only">
                <?= _("Password"); ?>
            </label>
			<input type="password"
                   name="login[password]"
                   id="inputPassword"
                   class="form-control"
                   placeholder="<?= _("Password"); ?>"
                   required>
		</div>

		<div class="checkbox">
			<label>
                <input type="checkbox"
                       value="1"
                       name="login[remember]"> <?= _("Remember me"); ?>
            </label>
		</div>

		<?php
            if (!Helpers_General::is_development_env()) {
                echo \Helpers\CaptchaHelper::getCaptcha();
            }
        ?>

		<button class="btn btn-lg btn-primary btn-block" type="submit">
			<?= _("Sign in"); ?>
		</button>
	</form>
    
    <?php 
        if (!empty($sign_up_link)):
    ?>
            <div>
                <p class="text-center">
                    <?= _("Don't have an account?"); ?>
                    <b><a href="<?= $sign_up_link; ?>"><?= _("Sign up"); ?></a></b>
                </p>
            </div>
    <?php
        endif;
    ?>
    <div>
        <p class="text-center">
            <b><a href="/password/lost"><?= _("Forgotten password"); ?></a></b>
        </p>
    </div>
</div>
