<?php include(APPPATH . "views/whitelabel/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/affs/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?php echo _("Edit e-mail"); ?> <small><?php echo Security::htmlentities($user['email']); ?></small></h2>
		<p class="help-block">
            <?php echo _("You can edit affiliate e-mail here."); ?>
        </p>
		<a href="/affs/list/view/<?php echo $user['token']; ?><?php echo Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?php echo _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" autocomplete="off" action="/affs/list/email/<?php echo $user['token']; ?><?php echo Lotto_View::query_vars(); ?>">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                    ?>
                    <div class="form-group<?php if (isset($errors['input.email'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputEmail">
                            <?php echo _("E-mail"); ?>:
                        </label>
                        <input type="email" required="required" autofocus 
                               value="<?php echo Security::htmlentities(null !== Input::post("input.email") ? Input::post("input.email") : $user['email']); ?>" 
                               class="form-control" 
                               id="inputEmail" 
                               name="input[email]" 
                               placeholder="<?php echo _("New e-mail"); ?>">
                     </div>
                    <button type="submit" class="btn btn-primary">
                        <?php echo _("Submit"); ?>
                    </button>
				</form>
			</div>
        </div>
    </div>
</div>
</div>
