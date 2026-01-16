<?php include(APPPATH . "views/whitelabel/shared/navbar.php"); ?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2><?= _("New blocked country"); ?></h2>
        <p class="help-block"><?= _("You can add new blocked countries here."); ?></p>
        <a href="/blocked_countries" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?></a>
        <div class="container-fluid container-admin row">
            <?php include(APPPATH . "views/whitelabel/shared/messages.php"); ?>
            <div class="col-md-6">
                <form method="post" action="/blocked_countries/add">
                    <div class="form-group<?= $input_has_error('input.code') ?>">
                        <label class="control-label" for="inputCode"><?= _("Country"); ?>:</label>
                        <select name="input[code]" id="inputCode" class="form-control">
                            <?php foreach ($countries AS $key => $country): ?>
                                <option value="<?= $key ?>" <?= $post_selected("input.code", $key) ?>><?= $country ?></option>
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
