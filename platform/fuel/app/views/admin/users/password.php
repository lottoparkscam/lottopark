<?php 
include(APPPATH."views/admin/shared/navbar.php");

$ending_url = $user['id'] . Lotto_View::query_vars();

$urls = [
    'back' => '/users/view/' . $ending_url,
    'action' => '/users/password/' . $ending_url,
];
?>

<div class="container-fluid">
	<div class="col-md-2">
		<?php 
            include(APPPATH."views/admin/users/menu.php");
        ?>
	</div>
    
	<div class="col-md-10">
		<h2>
            <?= _("Change password"); ?> <small><?= Security::htmlentities($user['email']); ?></small>
        </h2>
		<p class="help-block">
            <?= _("You can change user password here."); ?>
        </p>
		<a href="<?= $urls['back']; ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" autocomplete="off" action="<?= $urls['action']; ?>">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                        
                        $password_error_class = '';
                        if (isset($errors['input.password'])) {
                            $password_error_class = ' has-error';
                        }
                    ?>
                    
                    <div class="form-group<?= $password_error_class; ?>">
                        <label class="control-label" for="inputPassword">
                            <?= _("New password"); ?>:
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   required="required" 
                                   autofocus 
                                   class="form-control clear" 
                                   id="inputPassword" 
                                   name="input[password]" 
                                   placeholder="<?= _("New password"); ?>">
                            <span class="input-group-btn">
                                <button type="button" 
                                        class="btn btn-default" 
                                        id="generatePassword">
                                    <span class="glyphicon glyphicon-refresh"></span> <?= _("Random"); ?>
                                </button>
                            </span>
                        </div>
                        <p class="help-block" id="generatedPassword">
                            <?= _("Generated password"); ?>: <span></span>
                        </p>
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
