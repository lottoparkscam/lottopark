<?php 
include(APPPATH . "views/admin/shared/navbar.php");

$ending_url = $user['id'] . Lotto_View::query_vars();

$view_url = '/users/view/' . $ending_url;
$action_url = '/users/email/' . $ending_url;
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/admin/users/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?= _("Edit e-mail"); ?> <small><?= Security::htmlentities($user['email']); ?></small></h2>
		<p class="help-block">
            <?= _("You can edit user e-mail here."); ?>
        </p>
		<a href="<?= $view_url; ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" autocomplete="off" action="<?= $action_url; ?>">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                        
                        $email_error_class = '';
                        if (isset($errors['input.email'])) {
                            $email_error_class = ' has-error';
                        }
                        
                        $email_value_t = $user['email'];
                        if (null !== Input::post("input.email")) {
                            $email_value_t = Input::post("input.email");
                        }
                        $email_value = Security::htmlentities($email_value_t);
                    ?>
                    <div class="form-group<?= $email_error_class; ?>">
                        <label class="control-label" for="inputEmail">
                            <?= _("E-mail"); ?>:
                        </label>
                        <input type="email" 
                               required="required" 
                               autofocus 
                               value="<?= $email_value; ?>" 
                               class="form-control" 
                               id="inputEmail" 
                               name="input[email]" 
                               placeholder="<?= _("New e-mail"); ?>">
                     </div>
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
				</form>
			</div>
        </div>
    </div>
</div>
</div>
