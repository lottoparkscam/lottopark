<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
        include(APPPATH . "views/whitelabel/tickets/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Multi-draw Ticket Cancellation"); ?>
        </h2>
        <p class="help-block">
            <?= _("Multi-draw ticket cancellation details"); ?>
        </p>
        
        <a href="/multidraw_tickets" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?php echo _("Back"); ?>
        </a>

        <div class="multidraw-details">
			<span class="details-label">
                    <?= Security::htmlentities(_("Multidraw Token")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($multidraw['token_with_prefix']) ? strtoupper($multidraw['token_with_prefix']) : _("-")); ?>
            </span>
            <br>
            <span class="details-label">
                    <?= Security::htmlentities(_("User will receive")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($cancellation['amount']) ? strtoupper($cancellation['amount']) : _("-")); ?> 
            </span>
            <br/>
            <span class="details-label">
                    <?= Security::htmlentities(_("Total number of multidraw tickets")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($cancellation['multidraw_tickets']) ? strtoupper($cancellation['multidraw_tickets']) : _("-")); ?>
            </span>
            <br>
            <span class="details-label">
                    <?= Security::htmlentities(_("Number of tickets that will be canceled")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($cancellation['tickets']) ? strtoupper($cancellation['tickets']) : _("-")); ?>
            </span>
            <br>
            <span class="details-label">
                    <?= Security::htmlentities(_("Number of tickets that have been processed")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($cancellation['processed_tickets']) ? strtoupper($cancellation['processed_tickets']) : _("-")); ?>
            </span>
            <br>
            <span class="details-label">
                    <?= Security::htmlentities(_("Single ticket cost")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($cancellation['single_ticket_amount']) ? strtoupper($cancellation['single_ticket_amount']) : _("-")); ?> 
            </span>
            <br>
            <span class="details-label">
                    <?= Security::htmlentities(_("Total multi-draw amount")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($cancellation['amount_md']) ? strtoupper($cancellation['amount_md']) : _("-")); ?>
            </span>
            <br><br>
            <span class="details-label">
                <?= Security::htmlentities(_("Token")); ?>:
            </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($user['token']) ? strtoupper($user['token']) : _("-")); ?>
            </span>
            <br/>
            <span class="details-label">
                    <?= Security::htmlentities(_("First Name")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($user['name']) ? $user['name'] : _("Anonymous")); ?>
                </span>
            <br>
            <span class="details-label">
                    <?= Security::htmlentities(_("Last Name")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($user['surname']) ? $user['surname'] : _("Anonymous")); ?>
                </span>
            <br>
            <span class="details-label">
                    <?= Security::htmlentities(_("E-mail")); ?>:
                </span>
            <span class="details-value">
                    <span class="<?= Lotto_View::show_boolean_class($user['is_confirmed']); ?>">
                        <?= Lotto_View::show_boolean($user['is_confirmed']); ?>
                    </span> <?= Security::htmlentities($user['email']); ?>
                </span>
            <br>
            <?php /*
                <a href="/affs/list/email/<?= $user['token']; ?><?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-edit"></span> <?= _("Edit E-mail"); ?></a><br>
                */ ?>
            <span class="details-label">
                    <?= Security::htmlentities(_("Country")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($user['country']) && !empty($countries[$user['country']]) ? $countries[$user['country']] : _("-")); ?>
                </span>
            <br>
            <span class="details-label">
                    <?= Security::htmlentities(_("City")); ?>:
                </span>
            <span class="details-value">
                    <?= Security::htmlentities(!empty($user['city']) ? $user['city'] : _("-")); ?>
                </span>
            <br/><br/>
            <button type="button"
                    data-href="/multidraw_tickets/cancellation/<?=$multidraw['token'];?>/confirm"
                    class="btn btn-sm btn-danger"
                    data-toggle="modal" data-target="#confirmModal"
                    data-confirm="<?= _("Are you sure?"); ?>">
                <span class="glyphicon glyphicon-remove"></span> <?= _("Confirm"); ?>
            </button>
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
                <button type="button" class="btn btn-default"  data-dismiss="modal">
                    <?= _("Cancel"); ?>
                </button>
            </div>
        </div>
    </div>
</div>