<?php include(APPPATH . "views/admin/shared/navbar.php"); ?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
            include(APPPATH . "views/admin/transactions/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Withdrawals"); ?>
        </h2>
        <p class="help-block">
            <?= _("Here you can manage your users' withdrawals."); ?>
        </p>


    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= _("Confirm"); ?></h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmOK" class="btn btn-success"><?= _("OK"); ?></a>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _("Cancel"); ?></button>
            </div>
        </div>
    </div>
</div>
