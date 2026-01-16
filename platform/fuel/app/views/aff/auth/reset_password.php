<div class="container">
	<form class="form-lost-password" method="post" action="">
		<?php include(APPPATH . "views/admin/shared/messages.php"); ?>
		<h2 class="form-lost-password-heading"> <?= _("Password Recovery"); ?></h2>
		<div class="form-group">
			<label for="inputPassword" class="sr-only"><?= _("New password"); ?></label>
			<input type="password" name="password" id="inputPassword" class="form-control" placeholder="<?= _("Provide new password..."); ?>" required autofocus>
		</div>
		<div class="form-group">
			<label for="inputRepeatPassword" class="sr-only"><?= _("Repeat new password"); ?></label>
			<input type="password" name="repeatPassword" id="inputRepeatPassword" class="form-control" placeholder="<?= _("Repeat new password..."); ?>" required>
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
			<?= _("Change"); ?>
		</button>
	</form>
</div>