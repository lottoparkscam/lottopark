<?php 
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/affs/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Commissions"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("Here you can view the latest commissions of your affiliates."); ?>
        </p>
        
        <?php
            include(APPPATH . "views/whitelabel/affs/reports/commissions_filters.php");
        ?>
        
        <div class="container-fluid container-admin">
            <?php 
                include(APPPATH . "views/aff/shared/messages.php");

                if (isset($commissions) && count($commissions) > 0):
                    echo $pages;
            ?>
                    <div class="pull-right export-view">
                        <a href="/affs/commissions/export<?= Lotto_View::query_vars(); ?>" 
                           class="btn btn-primary btn-xs">
                            <span class="glyphicon glyphicon-download-alt"></span> <?= _("Export View to CSV"); ?>
                        </a>
                    </div>

                    <div class="clearfix"></div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("Affiliate"); ?>
                                    </th>
                                    <th>
                                        <?= _("User"); ?>
                                    </th>
                                    <th>
                                        <?= _("Transaction ID"); ?>
                                    </th>
                                    <th>
                                        <?= _("Type"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Date"); ?>
                                    </th>
                                    <th>
                                        <?= _("Amount"); ?>
                                    </th>
                                    <th class="text-nowrap">
                                        <?= _("Ticket cost"); ?>
                                        <br>
                                        <?= _("Payment cost"); ?>
                                    </th>
                                    <th>
                                        <?= _("Income"); ?>
                                    </th>
                                    <th>
                                        <?= _("Commission"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Manage"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    foreach ($commissions as $commission):
                                ?>
                                        <tr>
                                            <td>
                                                <?= $commission['aff_full_name']; ?>
                                                <br>
                                                <?= $commission['aff_is_confirmed_span']; ?> 
                                                <?= $commission['aff_email']; ?>
                                                <br>
                                                <a href="<?= $commission['view_aff_url']; ?>" 
                                                   class="btn btn-xs btn-primary">
                                                    <span class="glyphicon glyphicon-th-list"></span> <?= _("View affiliate"); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?= $commission['user_full_name']; ?>
                                                <br>
                                                <?= $commission['user_is_confirmed_span']; ?> 
                                                <?= $commission['user_email']; ?>
                                                <?php 
                                                    if (!in_array($rparam, ["deleted", "inactive"])):
                                                ?>
                                                        <br>
                                                        <a href="<?= $commission['view_user_url']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View user"); ?>
                                                        </a>
                                                        <br>
                                                        <a href="<?= $commission['view_tickets_url']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                                                        </a>
                                                <?php 
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?= $commission['transaction_id']; ?>
                                                <br>
                                                <a href="<?= $commission['view_transaction_url']; ?>" 
                                                   class="btn btn-xs btn-primary">
                                                    <span class="glyphicon glyphicon-th-list"></span> <?= _("View transaction"); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?= $commission['aff_type']; ?>
                                                <br>
                                                <?= $commission['tier']; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $commission['date_confirmed']; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    echo $commission['amount_manager'];

                                                    if (!empty($commission['amounts_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php 
                                                                    echo $commission['amounts_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php 
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    echo $commission['cost_manager'];

                                                    if (!empty($commission['costs_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php 
                                                                    echo $commission['costs_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php 
                                                    endif;
                                                ?>
                                                <br>
                                                <?php 
                                                    echo $commission['payment_cost_manager'];

                                                    if (!empty($commission['payment_costs_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php 
                                                                    echo $commission['payment_costs_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php 
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    echo $commission['real_income_manager'];

                                                    if (!empty($commission['real_incomes_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php 
                                                                    echo $commission['real_incomes_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php 
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    echo $commission['commission_manager'];

                                                    if (!empty($commission['commissions_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php 
                                                                    echo $commission['commissions_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php 
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                    if ($commission['user_is_accepted'] == 0):
                                                ?>
                                                        <button type="button" <?= $commission['accept_disabled']; ?> 
                                                                data-href="<?= $commission['accept_url']; ?>" 
                                                                class="btn btn-xs btn-success" 
                                                                data-toggle="modal" 
                                                                data-target="#confirmModal" 
                                                                data-confirm="<?= _("Are you sure?"); ?>">
                                                            <span class="glyphicon glyphicon-ok"></span> <?= _("Accept"); ?>
                                                        </button>
                                                <?php 
                                                    endif;
                                                ?>
                                                <button type="button" <?= $commission['delete_disabled']; ?> 
                                                        data-href="<?= $commission['delete_url']; ?>" 
                                                        class="btn btn-xs btn-danger" 
                                                        data-toggle="modal" 
                                                        data-target="#confirmModal" 
                                                        data-confirm="<?= _("Are you sure?"); ?>">
                                                    <span class="glyphicon glyphicon-remove"></span> <?= _("Delete"); ?>
                                                </button>
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
                        <?= _("No commissions."); ?>
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