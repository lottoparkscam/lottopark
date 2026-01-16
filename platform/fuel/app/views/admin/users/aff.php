<?php 
    include(APPPATH . "views/admin/shared/navbar.php");
    
    $ending_url = Lotto_View::query_vars();
    
    $urls = [
        'back' => '/' . $rparam . $ending_url,
        'action' => '/' . $rparam . '/aff/' . $user['token'] . $ending_url
    ];
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php 
            include(APPPATH . "views/admin/users/menu.php");
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
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                        
                        $aff_class_error = '';
                        if (isset($errors['input.aff'])) {
                            $aff_class_error = ' has-error';
                        }
                    ?>
                    <div class="form-group <?= $aff_class_error; ?>">
                        <label class="control-label" for="inputParent">
                            <?= _("Affiliate"); ?>:
                        </label>
                        <select name="input[aff]" id="inputAff" class="form-control">
                            <option value="0">
                                <?= _("None"); ?>
                            </option>
                            <?php 
                                foreach ($kaffs as $key => $aff):
                                    if ($aff['is_deleted'] == 0 &&
                                        $aff['is_active'] == 1 &&
                                        $aff['is_accepted'] == 1
                                    ):
                                        $is_selected = '';
                                        if ((Input::get("input.aff") !== null &&
                                                Input::get("input.aff") == $key) ||
                                            (Input::get("input.aff") === null &&
                                                !empty($uac) &&
                                                count($uac) > 0 &&
                                                $uac[0]['whitelabel_aff_id'] == $aff['id'])
                                        ) {
                                            $is_selected = ' selected="selected"';
                                        }
                                        
                                        $aff_value_t = '';
                                        if (!empty($aff['name']) || !empty($aff['surname'])) {
                                            $name_f = $aff['name'] . ' ' . $aff['surname'];
                                            $aff_value_t = $name_f;
                                        } else {
                                            $aff_value_t = _("anonymous");
                                        }
                                        $aff_value_t .= ' &bull; ';
                                        $aff_value_t .= $aff['login'];
                                        
                                        $aff_value = Security::htmlentities($aff_value_t);
                            ?>
                                        <option value="<?= $key; ?>"<?= $is_selected; ?>>
                                            <?= $aff_value; ?>
                                        </option>
                            <?php 
                                    endif;
                                endforeach;
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
				</form>
			</div>
        </div>
    </div>
</div>
