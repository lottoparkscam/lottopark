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
            <?= _("Here you can enable lotteries for multi-draws."); ?>
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
                    <div class="col-md-8" style="padding-left:0;padding-top:30px;">
                        <?php
                        foreach ($whitelabel_lotteries as $id => $row) {
                            ?>
                            <div class="col-md-3" style="padding-left:0;">
                                <label>
                                    <input type="checkbox"
                                           name="lotteries[]"
                                           value="<?=$row['id']; ?>" <?=(in_array($row['id'], $lotteries)) ? 'checked' : ''?>>
                                    <?= $row['name']; ?>
                                </label>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="col-md-12 remove-sides-padding" style="padding-top:3 0px;">
                        <div class="form-group">
                            <button type="submit" name="submit" value="submit" class="btn btn-primary"><?php echo _("Save"); ?></button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
