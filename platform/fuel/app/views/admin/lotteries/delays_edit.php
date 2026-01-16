<?php include(APPPATH . "views/admin/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/admin/lotteries/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Add new postponed draw"); ?>
        </h2>
		<p class="help-block">
            <span class="text-warning"><?= _("You can add a postponed lottery draw here."); ?></span>
        </p>
		<a href="/lotteries/delays<?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" action="/lotteries/delays/<?= isset($edit) ? 'edit/'.$edit['id'] : 'new'; ?>">
					<?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                    ?>
					<div class="form-group<?php if (isset($errors['input.lottery'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputLottery">
                            <?= _("Lottery"); ?>:
                        </label>
                        <select autofocus required name="input[lottery]" id="inputLottery" class="form-control">
                            <?php 
                                foreach ($lotteries['__by_id'] as $key => $lottery):
                            ?>
                                    <option value="<?= $lottery['id']; ?>"<?php 
                                        if ((Input::post("input.lottery") !== null &&
                                                Input::post("input.lottery") == $lottery['id']) ||
                                            (Input::post("input.lottery") === null &&
                                                isset($edit) &&
                                                $edit['lottery_id'] == $lottery['id'])
                                        ):
                                            echo ' selected="selected"';
                                        endif;
                                    ?>>
                                        <?= Security::htmlentities($lottery['name']); ?>
                                    </option>
                            <?php 
                                endforeach;
                            ?>
                        </select>
                    </div>
                    <div class="form-group<?php if (isset($errors['input.datelocal'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputDate">
                            <?= _("Original date"); ?>:
                        </label>
                        <input type="text" value="<?php 
                                if (Input::post("input.datelocal") !== null):
                                    echo Security::htmlentities(Input::post("input.datelocal"));
                                else:
                                    if (isset($edit)):
                                        echo $datelocal->format("m/d/Y");
                                    endif;
                                endif;
                            ?>" 
                                required="required" class="form-control datepicker" id="inputDate" data-date-start-date="0d" 
                                data-date-max-view-mode="0" data-date-week-start="<?= Lotto_View::get_first_day_of_week(); ?>" 
                                name="input[datelocal]" placeholder="<?= _("Enter date"); ?>">
                        <p class="help-block">
                            <?= _("Format: mm/dd/yyyy"); ?>
                        </p>
                    </div>
                    <div class="form-group<?php if (isset($errors['input.datedelay'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputDateDelay">
                            <?= _("Delayed date"); ?>:
                        </label>
                        <input type="text" value="<?php 
                                if (Input::post("input.datedelay") !== null):
                                    echo Security::htmlentities(Input::post("input.datedelay"));
                                else:
                                    if (isset($edit)):
                                        echo $datedelay->format("m/d/Y");
                                    endif;
                                endif;
                            ?>" required="required" class="form-control datepicker" id="inputDateDelay" 
                            data-date-start-date="0d" data-date-max-view-mode="0" 
                            data-date-week-start="<?= Lotto_View::get_first_day_of_week(); ?>" 
                            name="input[datedelay]" placeholder="<?= _("Enter date"); ?>">
                        <p class="help-block">
                            <?= _("Format: mm/dd/yyyy"); ?>
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
