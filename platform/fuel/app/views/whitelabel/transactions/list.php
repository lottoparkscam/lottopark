<?php

use Models\Whitelabel;

include(APPPATH . "views/whitelabel/shared/navbar.php");

/** @var Whitelabel $whitelabelModel */
$whitelabelModel = Container::get('whitelabel');

?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/transactions/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= $title; ?>
        </h2>
        <p class="help-block">
            <?= $main_help_block_text; ?>
        </p>
        <?php
            include(APPPATH . 'views/whitelabel/transactions/index_filters.php');
        ?>
        
        <div class="container-fluid container-admin">
        <?php
            include(APPPATH . "views/whitelabel/shared/messages.php");
            
            if (isset($transactions) && count($transactions) > 0):
                echo $pages;
        ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                            <tr>
                                <th class="tablesorter-header tablesorter-<?= $sort['id']['class']; ?>" 
                                    data-href="<?= $sort['id']['link']; ?>">
                                        <?= _("ID"); ?>
                                </th>
                                <th>
                                    <?= _("User ID &bull; User Name"); ?>
                                    <br>
                                    <?= $whitelabelModel->loginForUserIsUsedDuringRegistration() ? _("E-mail &bull; Login") : _("E-mail"); ?>
                                </th>
                                <th>
                                    <?= _("Method"); ?>
                                </th>
                                <th class="tablesorter-header tablesorter-<?= $sort['amount']['class']; ?>" 
                                    data-href="<?= $sort['amount']['link']; ?>">
                                        <?= _("Amount"); ?>
                                </th>
                                <th>
                                    <?= _("Bonus amount"); ?>
                                </th>
                                <th class="text-center tablesorter-header tablesorter-<?= $sort['id']['class']; ?>" 
                                    data-href="<?= $sort['id']['link']; ?>">
                                        <?= _("Date"); ?>
                                </th>
                                <th class="text-center tablesorter-header tablesorter-<?= $sort['date_confirmed']['class']; ?>" 
                                    data-href="<?= $sort['date_confirmed']['link']; ?>">
                                        <?= _("Date confirmed"); ?>
                                </th>
                                <th>
                                    <?= _("Status"); ?>
                                </th>
                                <?php
                                    if ((string)$type === "transactions"):
                                ?>
                                        <th class="text-center">
                                            <?= _("Tickets/Processed"); ?>
                                        </th>
                                <?php
                                    endif;
                                ?>
                                <th class="text-center">
                                    <?= _("Manage"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($transactions as $transaction):
                            ?>
                                    <tr>
                                        <td>
                                            <?= $transaction["full_token"]; ?>
                                        </td>
                                        <td class="text-nowrap">
                                            <?= $transaction["user_data"]; ?> 
                                            <br>
                                            <?php
                                                echo $transaction["email"];
                                                if ($whitelabelModel->loginForUserIsUsedDuringRegistration()) {
                                                    echo " &bull; ";
                                                    echo $transaction["user_login"];
                                                }

                                                if ($transaction["is_deleted"]):
                                            ?>
                                                    <br>
                                                    <span class="text-danger">
                                                        <?= _("Deleted"); ?>
                                                    </span>
                                            <?php
                                                endif;
                                            ?>
                                            <br>
                                            <a href="<?= $transaction["user_view_url"]; ?>" 
                                               class="btn btn-xs btn-primary">
                                                <span class="glyphicon glyphicon-user"></span> <?= _("View user"); ?>
                                            </a>
                                            <?php
                                                if ($transaction["show_view_button"]):
                                            ?>
                                                    <br>
                                                    <a href="<?= $transaction["ticket_url"]; ?>" 
                                                       class="btn btn-xs btn-primary">
                                                        <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                                                    </a>
                                            <?php
                                                endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                echo $transaction["payment_method_name"];
                                                
                                                if ($transaction["show_payment_cost"]):
                                                    echo $transaction['payment_cost_manager_text'];
                        
                                                    if (!empty($transaction['payment_cost'])):
                                            ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?= $transaction['payment_cost']; ?>">
                                                            </span>
                                                        </small>
                                            <?php
                                                    endif;
                                                endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php

                                            ?>
                                            <span>
                                                <?php
                                                    echo $transaction["amount_manager_text"];
                                                    
                                                    if (!empty($transaction["user_amounts"])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?= $transaction["user_amounts"]; ?>">
                                                            </span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span>
                                                <?php
                                                    echo $transaction["bonus_amount_manager_text"];
                                                    
                                                    if (!empty($transaction["bonus_amounts"])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?= $transaction["bonus_amounts"]; ?>">
                                                            </span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?= $transaction["date"]; ?>
                                        </td>
                                        <td class="text-center">
                                            <?= $transaction["date_confirmed"]; ?>
                                        </td>
                                        <td>
                                            <span class="<?= $transaction['status_show_class']; ?>">
                                                <?= $transaction['status']; ?>
                                            </span>
                                        </td>
                                        <?php
                                            if ((string)$type === "transactions"):
                                        ?>
                                                <td class="text-center">
                                                    <?= $transaction['counted_text']; ?>
                                                </td>
                                        <?php
                                            endif;

                                            
                                        ?>
                                        <td class="text-center">
                                            <a href="<?= $transaction["view_url"]; ?>" 
                                               class="btn btn-xs btn-primary">
                                                <span class="glyphicon glyphicon-list"></span> <?= _("Details"); ?>
                                            </a>
                                            <?php
                                                if ($transaction["show_accept_button"]):
                                            ?>
                                                    <a href="<?= $transaction["accept_url"]; ?>" 
                                                       class="btn btn-xs btn-success">
                                                        <span class="glyphicon glyphicon-ok"></span> <?= _("Accept"); ?>
                                                    </a>
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
                <p class="text-info"><?= _("No transactions."); ?></p>
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
