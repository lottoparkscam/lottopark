<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
        include(APPPATH . "views/whitelabel/affs/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= $group_data['title']; ?>
        </h2>
        <p class="help-block">
            <?= _("You can add or edit affiliate group here."); ?>
        </p>
        <a href="<?= $group_data['back_url']; ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" autocomplete="off" action="<?= $group_data['action_url']; ?>">
                    <?php
                    if (!empty($this->errors)) {
                        include(APPPATH . "views/whitelabel/shared/errors.php");
                    }

                    // If this is not default group or totally new one row
                    if (!$edit_def) :
                        $name_error_class = '';
                        if (isset($errors['input.name'])) {
                            $name_error_class = ' has-error';
                        }
                    ?>
                        <div class="form-group <?= $name_error_class; ?>">
                            <label class="control-label" for="inputName">
                                <?= _("Name"); ?>:
                            </label>
                            <input type="text" autofocus required value="<?= $group_data['name']; ?>" class="form-control" id="inputName" name="input[name]" placeholder="<?= _("Enter name"); ?>">
                        </div>
                    <?php
                    endif;

                    $commission_value_error_class = '';
                    if (isset($errors['input.commissionvalue'])) {
                        $commission_value_error_class = ' has-error';
                    }
                    ?>
                    <div class="form-group <?= $commission_value_error_class; ?>">
                        <label class="control-label" for="inputCommissionValue">
                            <?= _("1st-tier sale commission value"); ?>:
                        </label>
                        <div class="input-group">
                            <input type="text" value="<?= $group_data['commission_value']; ?>" class="form-control" id="inputCommissionValue" name="input[commissionvalue]" placeholder="<?= _("Enter commission value"); ?>">
                            <div class="input-group-addon">
                                <?= _("%") ?>
                            </div>
                        </div>
                    </div>

                    <?php
                    $commissionvalue2_value_error_class = '';
                    if (isset($errors['input.commissionvalue2'])) {
                        $commissionvalue2_value_error_class = ' has-error';
                    }
                    ?>
                    <div class="form-group <?= $commissionvalue2_value_error_class; ?>">
                        <label class="control-label" for="inputCommissionValue2">
                            <?= _("2nd-tier sale commission value"); ?>:
                        </label>
                        <div class="input-group">
                            <input type="text" value="<?= $group_data['commission_value_2']; ?>" class="form-control" id="inputCommissionValue2" name="input[commissionvalue2]" placeholder="<?= _("Enter commission value"); ?>">
                            <div class="input-group-addon">
                                <?= _("%") ?>
                            </div>
                        </div>
                    </div>

                    <?php
                    $ftpcommissionvalue_error_class = '';
                    if (isset($errors['input.ftpcommissionvalue'])) {
                        $ftpcommissionvalue_error_class = ' has-error';
                    }
                    ?>
                    <div class="form-group <?= $ftpcommissionvalue_error_class; ?>">
                        <label class="control-label" for="inputFTPCommissionValue">
                            <?= _("1st-tier First Time Purchase commission value"); ?>:
                        </label>
                        <div class="input-group">
                            <input type="text" value="<?= $group_data['ftp_commission_value']; ?>" class="form-control" id="inputFTPCommissionValue" name="input[ftpcommissionvalue]" placeholder="<?= _("Enter commission value"); ?>">
                            <div class="input-group-addon">
                                <?= _("%") ?>
                            </div>
                        </div>
                    </div>

                    <?php
                    $ftpcommissionvalue2_error_class = '';
                    if (isset($errors['input.ftpcommissionvalue2'])) {
                        $ftpcommissionvalue2_error_class = ' has-error';
                    }
                    ?>
                    <div class="form-group <?= $ftpcommissionvalue2_error_class; ?>">
                        <label class="control-label" for="inputFTPCommissionValue2">
                            <?= _("2nd-tier First Time Purchase commission value"); ?>:
                        </label>
                        <div class="input-group">
                            <input type="text" value="<?= $group_data['ftp_commission_value_2'] ?>" class="form-control" id="inputFTPCommissionValue2" name="input[ftpcommissionvalue2]" placeholder="<?= _("Enter commission value"); ?>">
                            <div class="input-group-addon">
                                <?= _("%") ?>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>