<?php 
    include(APPPATH . "views/whitelabel/shared/navbar.php");
    
    $ending_url = Lotto_View::query_vars();
    
    $urls = [
        'back' => '/' . $rparam . $ending_url,
        'action' => '/' . $rparam . '/aff/' . $user['token'] . $ending_url
    ];
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php 
            include(APPPATH . "views/whitelabel/users/menu.php");
        ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Change affiliate"); ?> <small><?= Security::htmlentities($user['email']); ?></small>
        </h2>
		<p class="help-block">
            <?= _("You can change your user affiliate here."); ?>
        </p>
		<a href="<?= $urls['back']; ?>" 
           class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" action="<?= $urls['action']; ?>">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                        
                        $aff_class_error = '';
                        if (isset($errors['input.aff'])) {
                            $aff_class_error = ' has-error';
                        }
                    ?>
                    <div class="form-group <?= $aff_class_error; ?>">
                        <label class="control-label" for="inputAff"><?= _("Affiliate (optional):") ?></label>
                        <input type="email" 
                            value="<?= $affiliateEmail; ?>" 
                            class="form-control" 
                            id="inputAff" 
                            name="input[aff]" 
                            placeholder="<?= _("E-mail"); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
				</form>
			</div>
        </div>
    </div>
</div>
