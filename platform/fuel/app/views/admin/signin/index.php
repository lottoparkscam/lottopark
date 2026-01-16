<div class="container">
	<form class="form-signin" method="post" action="/">
		<?php 
            echo \Form::csrf();
            
            if (isset($this->errors)) {
                include(APPPATH . "views/admin/shared/errors.php");
            }
        ?>
		<h2 class="form-signin-heading"><?= _("Please sign in"); ?></h2>
		<div class="form-group <?php if (isset($this->errors)): echo ' has-error'; endif; ?>">
			<label for="inputLogin" class="sr-only"><?= _("Login"); ?></label>
			<input type="text" name="login[name]" id="inputLogin" 
                   class="form-control" placeholder="<?= _("Login"); ?>" 
                   required autofocus>
		</div>
		<div class="form-group <?php if (isset($this->errors)): echo ' has-error'; endif; ?>">
			<label for="inputPassword" class="sr-only"><?= _("Password"); ?></label>
			<input type="password" name="login[password]" id="inputPassword" 
                   class="form-control" placeholder="<?= _("Password"); ?>" 
                   required>
		</div>
		<div class="checkbox">
			<label><input type="checkbox" value="1" 
                          name="login[remember]"> <?= _("Remember me"); ?></label>
		</div>
		<button class="btn btn-lg btn-primary btn-block" type="submit">
			<?= _("Sign in"); ?>
		</button>
	</form>
</div>