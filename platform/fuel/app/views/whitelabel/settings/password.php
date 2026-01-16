<?php include(APPPATH . "views/whitelabel/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?php echo _("Change password"); ?></h2>
		<p class="help-block"><?php echo _("You can edit your password here."); ?></p>
		<a href="/account" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-chevron-left"></span> <?php echo _("Back"); ?></a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" autocomplete="off" action="/account/password">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                    ?>
                    <div class="form-group<?php if (isset($errors['input.password'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputPassword"><?php echo _("New password"); ?>:</label>
                        <div class="input-group">
                            <input type="password" required="required" autofocus class="form-control clear" id="inputPassword" name="input[password]" placeholder="<?php echo _("New password"); ?>">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" id="generatePassword"><span class="glyphicon glyphicon-refresh"></span> <?php echo _("Random"); ?></span></button>
                            </span>
                        </div>
                        <p class="help-block" id="generatedPassword"><?php echo _("Generated password"); ?>: <span></span></p>
                     </div>
                    <button type="submit" class="btn btn-primary"><?php echo _("Submit"); ?></button>
				</form>
			</div>
        </div>
    </div>
</div>
</div>
