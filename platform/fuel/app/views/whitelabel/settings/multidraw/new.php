<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Multi-draws settings"); ?>
        </h2>

        <div class="pull-right">
            <a href="/multidrawsettings/new" class="btn btn-success">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add New Option"); ?>
            </a>
        </div>

        <p class="help-block">
            <?= _("You can add multi-draw options here."); ?>
        </p>

        <div class="container-fluid container-admin row">
            <div class="col-md-12">
                <?php if (isset($this->errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <?php echo '<p>'.$error.'</p>'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="">
                    <div class="col-md-12 remove-sides-padding">
                        <div class="form-group col-md-6 remove-sides-padding">
                            <label for="campaign"><?php echo _("Number of draws"); ?>:</label>
                            <input type="number" min="1" max="255" class="form-control" id="draws" name="draws" value="1" placeholder="<?php echo _("Enter number of draws"); ?>">
                        </div>
                    </div>
                    <div class="col-md-12 remove-sides-padding">
                        <div class="form-group col-md-6 remove-sides-padding">
                            <label for="campaign"><?php echo _("Discount"); ?>:</label>
                            <input type="number" min="0" max="99" step="0.25" value="0.00" class="form-control" id="discount" name="discount" placeholder="<?php echo _("Enter discount"); ?>">
                        </div>
                    </div>
                    <div class="col-md-12 remove-sides-padding">
                        <div class="form-group">
                        <button type="submit" name="submit" value="submit" class="btn btn-primary"><?php echo _("Add option"); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
