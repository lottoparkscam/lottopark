<?php
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/transactions/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Withdrawals"); ?>
        </h2>
        <p class="help-block">
            <?= _("Here you can manage your users' withdrawals."); ?>
        </p>
        
        <?php
            include(APPPATH . "views/whitelabel/transactions/withdrawals_filters.php");
        ?>
        
        <div class="container-fluid container-admin">
        <?php
            include(APPPATH . "views/whitelabel/shared/messages.php");
            
            if (isset($withdrawals) && count($withdrawals) > 0):
                echo $pages;
        ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                            <tr>
                                <th>
                                    <?= _("ID"); ?>
                                </th>
                                <th>
                                    <?= _("User ID &bull; User Name"); ?>
                                    <br>
                                    <?= _("E-mail"); ?>
                                </th>
                                <th>
                                    <?= _("Method"); ?>
                                </th>
                                <th>
                                    <?= _("User balance"); ?>
                                </th>
                                <th class="tablesorter-header tablesorter-<?= $sort['amount']['class']; ?>" 
                                    data-href="<?= $sort['amount']['link']; ?>">
                                        <?= _("Amount"); ?>
                                </th>
                                <th class="text-center tablesorter-header tablesorter-<?= $sort['id']['class']; ?>" 
                                    data-href="<?= $sort['id']['link']; ?>">
                                        <?= _("Date"); ?>
                                </th>
                                <th class="text-center tablesorter-header tablesorter-<?= $sort['date_confirmed']['class']; ?>" 
                                    data-href="<?= $sort['date_confirmed']['link']; ?>">
                                        <?= _("Date approved"); ?>
                                </th>
                                <th>
                                    <?= _("Status"); ?>
                                </th>
                                <th class="text-center">
                                    <?= _("Manage"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($withdrawals as $withdrawal):
                                    $withdrawal_data = Forms_Whitelabel_Withdrawal_List::prepare_single_to_show(
                                        $whitelabel,
                                        $withdrawal
                                    );
                                    
                                    $start_url = "/withdrawals/";
                                    $end_url = $withdrawal['token'] . Lotto_View::query_vars();
                                    $withdrawal_urls = [
                                        'view' => $start_url . 'view/' . $end_url,
                                        'approve' => $start_url . 'approve/' . $end_url,
                                        'decline' => $start_url . 'decline/' . $end_url,
                                        'cancel' => $start_url . 'cancel/' . $end_url,
                                    ];
                            ?>
                                    <tr>
                                        <td>
                                            <?= $withdrawal_data['token']; ?>
                                        </td>
                                        <td class="text-nowrap">
                                            <?= $withdrawal_data['user_data_full']; ?>
                                            <br>
                                            <?php
                                                echo $withdrawal_data['email'];
                                                
                                                if ($withdrawal['is_deleted']):
                                            ?>
                                                    <br>
                                                    <span class="text-danger">
                                                        <?= _("Deleted"); ?>
                                                    </span>
                                            <?php
                                                endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?= _($withdrawal['wname']); ?>
                                        </td>
                                        <td<?= $withdrawal_data['balance_danger_class']; ?>>
                                            <?php
                                                echo $withdrawal_data['balance_in_manager'];
                                                
                                                if ($withdrawal_data['user_balance_show']):
                                            ?>
                                                    <small>
                                                        <span class="glyphicon glyphicon-info-sign" 
                                                              data-toggle="tooltip" 
                                                              data-placement="top" 
                                                              title="" 
                                                              data-original-title="<?= $withdrawal_data['user_balance']; ?>">
                                                        </span>
                                                    </small>
                                            <?php
                                                endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                echo $withdrawal_data['amount_manager'];
                                                
                                                if ($withdrawal_data['user_amount_show']):
                                            ?>
                                                    <small>
                                                        <span class="glyphicon glyphicon-info-sign" 
                                                              data-toggle="tooltip" 
                                                              data-placement="top" 
                                                              title="" 
                                                              data-original-title="<?= $withdrawal_data['user_amount']; ?>">
                                                        </span>
                                                    </small>
                                            <?php
                                                endif;
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?= $withdrawal_data['date']; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                                if (!empty($withdrawal_data['date_confirmed'])) {
                                                    echo $withdrawal_data['date_confirmed'];
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="<?= $withdrawal_data['status_span_class'] ?>">
                                                <?= $withdrawal_data['status_text']; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= $withdrawal_urls['view']; ?>" 
                                               class="btn btn-xs btn-primary">
                                                <span class="glyphicon glyphicon-list"></span> <?= _("Details"); ?>
                                            </a>
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
                <p class="text-info"><?= _("No withdrawals."); ?></p>
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
                <h4 class="modal-title">
                    <?= _("Confirm"); ?>
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
