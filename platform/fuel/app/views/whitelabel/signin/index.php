<div class="container">
	<form class="form-signin" method="post" action="/">
		<?php 
            echo \Form::csrf();
            
            if (isset($this->errors)) {
                include(APPPATH . "views/whitelabel/shared/errors.php");
            }
        ?>
		<h2 class="form-signin-heading"><?php echo _("Please sign in"); ?></h2>
		<div class="form-group<?php if (isset($this->errors)): echo ' has-error'; endif; ?>">
			<label for="inputLogin" class="sr-only"><?php echo _("Login"); ?></label>
			<input type="text" name="login[name]" id="inputLogin" class="form-control" placeholder="<?php echo _("Login"); ?>" required autofocus>
		</div>
		<div class="form-group<?php if (isset($this->errors)): echo ' has-error'; endif; ?>">
			<label for="inputPassword" class="sr-only"><?php echo _("Password"); ?></label>
			<input type="password" name="login[password]" id="inputPassword" class="form-control" placeholder="<?php echo _("Password"); ?>" required>
		</div>
		<div class="checkbox">
			<label><input type="checkbox" value="1" name="login[remember]"> <?php echo _("Remember me"); ?></label>
		</div>
		<button class="btn btn-lg btn-primary btn-block" type="submit">
			<?php echo _("Sign in"); ?>
		</button>
	</form>
</div>