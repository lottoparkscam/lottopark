<?php

use Helpers\LogoHelper;

?>
<div class="container">
	<div class="aff-logo">
		<?= LogoHelper::getWhitelabelImgLogoSection() ?>
	</div>
	<form class="form-lost-password" method="post" action="">
		<?php include(APPPATH . "views/admin/shared/messages.php"); ?>
		<h2 class="form-lost-password-heading"> <?= _("Password Recovery"); ?></h2>
		<div class="form-group">
			<label for="inputEmail" class="sr-only"><?= _("Email"); ?></label>
			<input type="email" name="input[email]" id="inputEmail" class="form-control" placeholder="<?= _("Provide e-mail..."); ?>" required autofocus>
		</div>
		<div class="form-group">
			<?php
			if (!Helpers_General::is_development_env()) {
				echo \Helpers\CaptchaHelper::getCaptcha();
			}
			?>
		</div>
		<input type="hidden" name="action" value="process">
		<button class="btn btn-lg btn-primary btn-block" type="submit">
			<?= _("Send"); ?>
		</button>
	</form>
</div>