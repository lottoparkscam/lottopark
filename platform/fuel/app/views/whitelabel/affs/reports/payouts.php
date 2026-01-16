<?php include(APPPATH . "views/whitelabel/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/affs/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Payouts"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can view and manage your payouts. They are calculated automatically every month."); ?>
        </p>
        
        <?php
            include(APPPATH . "views/whitelabel/affs/reports/payouts_filters.php");
        ?>

		<div class="container-fluid container-admin">
            <?php 
                include(APPPATH . "views/aff/shared/messages.php");

                if (isset($payouts) && count($payouts) > 0):
                    echo $pages;
            ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("Affiliate"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Date"); ?>
                                    </th>
                                    <th>
                                        <?= _("Amount"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Commissions"); ?>
                                    </th>
                                    <th>
                                        <?= _("Payment"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Paid out"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Manage"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    foreach ($payouts as $payout):
                                ?>
                                        <tr>
                                            <td>
                                                <?= $payout['full_aff_name']; ?>
                                                <br>
                                                <span class="<?= $payout['is_confirmed_class']; ?>">
                                                    <?= $payout['is_confirmed_span']; ?>
                                                </span> <?= $payout['email']; ?>
                                                <br>
                                                <a href="<?= $payout['view_url']; ?>" 
                                                   class="btn btn-xs btn-primary">
                                                    <span class="glyphicon glyphicon-th-list"></span> <?= _("View affiliate"); ?>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <?= $payout['date']; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    echo $payout['amount_manager'];

                                                    if (!empty($payout['amounts_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php 
                                                                    echo $payout['amounts_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php 
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $payout['commissions']; ?>
                                            </td>
                                            <td>
                                                <?= $payout['payment']; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="<?= $payout['is_paidout_class']; ?>">
                                                    <?= $payout['is_paidout_span']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= $payout['report_url']; ?>" 
                                                   class="btn btn-xs btn-primary">
                                                    <span class="glyphicon glyphicon-list"></span> <?= _("View report"); ?>
                                                </a>
                                                <?php 
                                                    if ($payout['is_paidout'] === 0):
                                                ?>
                                                        <button type="button" 
                                                                data-href="<?= $payout['accept_url']; ?>" 
                                                                class="btn btn-xs btn-success" 
                                                                data-toggle="modal" 
                                                                data-target="#confirmModal" 
                                                                data-confirm="<?= _("Are you sure?"); ?>">
                                                            <span class="glyphicon glyphicon-ok"></span> <?= _("Mark as paid out"); ?>
                                                        </button>
                                                <?php 
                                                    endif;
                                                ?>
                                            </td>
                                        </tr>
                                <?php 
                                    endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
            <?php 
                    echo $pages;
                else:
            ?>
                    <p class="text-info">
                        <?= _("No payouts."); ?>
                    </p>
            <?php 
                endif;
            ?>
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
                <h4 class="modal-title"><?= _("Confirm"); ?></h4>
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