<?php include(APPPATH."views/admin/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH."views/admin/lotteries/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?= _("Change source"); ?> <small><?= Security::htmlentities($lottery['name']); ?></small></h2>
		<p class="help-block"><span class="text-warning"><?= _("Here you can change the source for the lottery. You should do this only when the original one is not working for a longer period of time!"); ?></span></p>
		<a href="/lotteries" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?></a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" action="/lotteries/source/<?= $lottery['id']; ?>">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                    ?>
                    <div class="form-group<?php if (isset($errors['input.source'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputDate">
                            <?= _("Source"); ?>:
                        </label>
                        <select class="form-control" name="input[source]">
                            <?php foreach ($sources as $source): ?>
                                <option value="<?= $source['id']; ?>"<?php if ($source['id'] == $lottery['source_id']): echo ' selected="selected"'; endif; ?>>
                                <?= Security::htmlentities($source['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= _("Submit"); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
