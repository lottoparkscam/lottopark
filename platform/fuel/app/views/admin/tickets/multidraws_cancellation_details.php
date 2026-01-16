<?php
include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
        include(APPPATH . "views/admin/tickets/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Massive Multi-draw Ticket Cancellation"); ?>
        </h2>
        <p class="help-block">
            <?= _("Massive Multi-draw ticket cancellation details"); ?>
        </p>

        <a href="/multidraw_tickets" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?php echo _("Back"); ?>
        </a>

        <div class="multidraw-details">
            <span class="details-label">
                    <?= Security::htmlentities(_("Lottery")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($details['lottery_name']) ? strtoupper($details['lottery_name']) : _("-")); ?>
            </span>
            <br/>
            <span class="details-label">
                    <?= Security::htmlentities(_("Range from")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($details['range_from']) ? strtoupper($details['range_from']) : _("-")); ?>
            </span>
            <br/>
            <span class="details-label">
                    <?= Security::htmlentities(_("Users will receive")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($cancellation['amount']) ? strtoupper($cancellation['amount']) : _("-")); ?> <?=$cancellation['currency'];?>
            </span>
            <br/>
            <span class="details-label">
                    <?= Security::htmlentities(_("Number of tickets that will be canceled")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($cancellation['tickets']) ? strtoupper($cancellation['tickets']) : _("-")); ?>
            </span>
            <br>
            <span class="details-label">
                    <?= Security::htmlentities(_("Number of users who participate")); ?>:
            </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($cancellation['users']) ? strtoupper($cancellation['users']) : _("-")); ?>
            </span>
            <br>
            <span class="details-label">
                    <?= Security::htmlentities(_("Whole transactions costs that users paid")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($cancellation['transactions_cost']) ? strtoupper($cancellation['transactions_cost']) : _("-")); ?> <?=$cancellation['currency'];?>
            </span>
            <br/><br/>
            <a href="/multidraw_tickets/confirm?lottery=<?=$details['lottery_id'];?>&range_from=<?=$details['range_from'];?>" class="btn btn-success btn-mt">
                <span class="glyphicon glyphicon-edit"></span> <?= _("Confirm"); ?>
            </a>
            <br>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <?= _("Are you sure?"); ?>
                </h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmOK" class="btn btn-success">
                    <?= _("OK"); ?>
                </a>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?= _("Cancel"); ?>
                </button>
            </div>
        </div>
    </div>
</div>