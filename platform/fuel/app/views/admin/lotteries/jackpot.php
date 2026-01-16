<?php include(APPPATH."views/admin/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH."views/admin/lotteries/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?= _("Edit Jackpot"); ?> <small><?= Security::htmlentities($lottery['name']); ?></small></h2>
		<p class="help-block"><span class="text-warning"><?= _("You can edit the current lottery jackpot here. You should only do this when the automated script does not work."); ?></span></p>
		<a href="/lotteries" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?></a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" action="/lotteries/jackpot/<?= $lottery['id']; ?>">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                    ?>
                    <div class="form-group<?php if (isset($errors['input.jackpot'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputJackpot">
                            <?= _("Current Jackpot"); ?>:
                        </label>
                        <input type="text" required="required" value="<?= Security::htmlentities(null !== Input::post("input.jackpot") ? Input::post("input.jackpot") : round($lottery['current_jackpot'], 2)); ?>" class="form-control" id="inputJackpot" name="input[jackpot]" placeholder="<?= _("Enter current jackpot"); ?>">
                        <p class="help-block">
                            <?= sprintf(_("Unit: million (e.g. type '29.5' for 29.5 million jackpot). Use dot for decimal digits. <br>Currency: %s"), Lotto_View::format_currency_code($currencies[$lottery['currency_id']]['code']), Lotto_View::format_currency($lottery['current_jackpot']*1000000, $currencies[$lottery['currency_id']]['code'])); ?>
                        </p>
                     </div>
                    <button type="submit" class="btn btn-primary"><?= _("Submit"); ?></button>
				</form>
			</div>
        </div>
    </div>
</div>
</div>
