<?php include(APPPATH."views/admin/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH."views/admin/lotteries/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?= _("Change next draw"); ?> <small><?= Security::htmlentities($lottery['name']); ?></small></h2>
		<p class="help-block">
            <span class="text-warning"><?= _("You can manually change the lottery draw date here. You should only do this and you have to do this only when the lottery draw has been moved! It will automatically move every ticket to the chosen next draw date. The operation cannot be undone!"); ?></span>
        </p>
		<a href="/lotteries" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?></a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" action="/lotteries/nextdraw/<?= $lottery['id']; ?>">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                    ?>
                    <div class="form-group<?php if (isset($errors['input.nextdraw'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputNextDraw">
                            <?= _("Next Draw Date"); ?>:
                        </label>
                        <?php $nextdraw = new DateTime($lottery['next_date_local'], new DateTimeZone("UTC")); ?>
                        <input type="text" required="required" 
                               value="<?= Security::htmlentities(null !== Input::post("input.nextdraw") ? Input::post("input.nextdraw") : $nextdraw->format('m/d/Y')); ?>" 
                               class="form-control datepicker" 
                               data-date-week-start="1" 
                               data-date-start-date="0d" 
                               data-date-days-of-week-highlighted="[<?= Lotto_View::get_highlighted_days_of_week($lottery); ?>]"
                               data-date-max-view-mode="0" id="inputNextDraw" 
                               name="input[nextdraw]" 
                               placeholder="<?= _("Next Draw Date"); ?>">
                        <input type="time" required="required" class="form-control" name="input[nextdrawtime]"
                               placeholder="<?= _("Time of the draw"); ?>">>
                        <p class="help-block">
                            <?= _("Format: mm/dd/yyyy<br><strong>Date should be in lottery local timezone!</strong>"); ?>
                        </p>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= _("Submit"); ?></button>
				</form>
			</div>
        </div>
    </div>
</div>
</div>
