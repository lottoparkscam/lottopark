<?php
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/transactions/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?php
                echo _("Transaction details");
            ?> 
            <small>
                <?= $transaction_data['full_token']; ?>
            </small>
        </h2>
        <p class="help-block">
            <?= _("You can view transaction details here."); ?>
        </p>
        <a href="<?= $transaction_urls['back']; ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
            <div class="container-fluid container-admin row">
            <?php
                include(APPPATH . "views/whitelabel/shared/messages.php");
            ?>
            <div class="col-md-10 user-details">
                <span class="details-label">
                    <?= Security::htmlentities(_("ID")); ?>:
                </span>
                <span class="details-value">
                    <?= $transaction_data['full_token']; ?>
                </span><br>
                <span class="details-label">
                    <?= Security::htmlentities(_("User ID")); ?>:
                </span>
                <span class="details-value">
                    <?= $transaction_data['user_full_token']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("First Name")); ?>:
                </span>
                <span class="details-value">
                    <?= $transaction_data['first_name']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Last Name")); ?>:
                </span>
                <span class="details-value">
                    <?= $transaction_data['surname']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("E-mail")); ?>:
                </span>
                <span class="details-value">
                    <?= $transaction_data['email']; ?>
                </span>
                <br>
                <?php
                /** @var Whitelabel $whitelabelModel */
                $whitelabelModel = Container::get('whitelabel');
                if ($whitelabelModel->loginForUserIsUsedDuringRegistration()):
                ?>
                        <span class="details-label">
                            <?= Security::htmlentities(_("Login")); ?>:
                        </span>
                        <span class="details-value">
                            <?= $transaction_data['user_login']; ?>
                        </span>
                        <br>
                <?php
                    endif
                ?>
                <span class="details-label">
                    <?= Security::htmlentities(_("Date")); ?>:
                </span>
                <span class="details-value">
                    <?= $transaction_data['date']; ?>
                </span>
                <br>
                <?php
                    if (!empty($transaction_data['date_confirmed'])):
                ?>
                        <span class="details-label">
                            <?= Security::htmlentities(_("Date confirmed")); ?>:
                        </span>
                        <span class="details-value">
                            <?= $transaction_data['date_confirmed']; ?>
                        </span>
                        <br>
                <?php
                    endif;
                    
                    if (!empty($transaction_data['payment_method_type'])):
                ?>
                        <span class="details-label">
                            <?= Security::htmlentities(_("Method")); ?>:
                        </span>
                        <span class="details-value">
                            <?= $transaction_data['payment_method_type']; ?>
                        </span>
                        <br>
                <?php
                    endif;
                    
                    if (!empty($transaction_data['gateway_payment_method'])):
                ?>
                        <span class="details-label">
                            <?= Security::htmlentities(_("Gateway")); ?>:
                        </span>
                        <span class="details-value">
                            <?= $transaction_data['gateway_payment_method']; ?>
                        </span>
                        <br>
                <?php
                    endif;
                ?>
                <span class="details-label">
                    <?= Security::htmlentities(_("Amount")); ?>:
                </span>
                <span class="details-value">
                    <?php
                        echo $transaction_data['amount_manager'];
                        
                        if (!empty($transaction_data['user_amounts'])):
                    ?>
                            <small>
                                <span class="glyphicon glyphicon-info-sign" 
                                      data-toggle="tooltip" 
                                      data-placement="top" 
                                      title="" 
                                      data-original-title="<?= $transaction_data['user_amounts']; ?>">
                                </span>
                            </small>
                    <?php
                        endif;
                    ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Bonus amount")); ?>:
                </span>
                <span class="details-value">
                    <?php
                        echo $transaction_data['bonus_amount_manager'];
                        
                        if (!empty($transaction_data['bonus_amounts'])):
                    ?>
                            <small>
                                <span class="glyphicon glyphicon-info-sign" 
                                      data-toggle="tooltip" 
                                      data-placement="top" 
                                      title="" 
                                      data-original-title="<?= $transaction_data['bonus_amounts']; ?>">
                                </span>
                            </small>
                    <?php
                        endif;
                    ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Income")); ?>:
                </span>
                <span class="details-value">
                    <?php
                        echo $transaction_data['income_manager'];
                        
                        if (!empty($transaction_data['income'])):
                    ?>
                            <small>
                                <span class="glyphicon glyphicon-info-sign" 
                                      data-toggle="tooltip" 
                                      data-placement="top" 
                                      title="" 
                                      data-original-title="<?= $transaction_data['income']; ?>">
                                </span>
                            </small>
                    <?php
                        endif;
                    ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Cost")); ?>:
                </span>
                <span class="details-value">
                    <?php
                        echo $transaction_data['cost_manager'];
                        
                        if (!empty($transaction_data['cost'])):
                    ?>
                            <small>
                                <span class="glyphicon glyphicon-info-sign" 
                                      data-toggle="tooltip" 
                                      data-placement="top" 
                                      title="" 
                                      data-original-title="<?= $transaction_data['cost']; ?>">
                                </span>
                            </small>
                    <?php
                        endif;
                    ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Payment cost")); ?>:
                </span>
                <span class="details-value">
                    <?php
                        echo $transaction_data['payment_cost_manager'];
                        
                        if (!empty($transaction_data['payment_cost'])):
                    ?>
                            <small>
                                <span class="glyphicon glyphicon-info-sign" 
                                      data-toggle="tooltip" 
                                      data-placement="top" 
                                      title="" 
                                      data-original-title="<?= $transaction_data['payment_cost']; ?>">
                                </span>
                            </small>
                    <?php
                        endif;
                    ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Royalties")); ?>:
                </span>
                <span class="details-value">
                    <?php
                        echo $transaction_data['margin_manager'];
                        
                        if (!empty($transaction_data['margin'])):
                    ?>
                            <small>
                                <span class="glyphicon glyphicon-info-sign" 
                                      data-toggle="tooltip" 
                                      data-placement="top" 
                                      title="" 
                                      data-original-title="<?= $transaction_data['margin']; ?>">
                                </span>
                            </small>
                    <?php
                        endif;
                    ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Status")); ?>:
                </span>
                <span class="details-value">
                    <span class="<?= $transaction_data['status_show_class']; ?>">
                        <?= $transaction_data['status']; ?>
                    </span>
                </span>
                <br>
                <?php
                    if (!empty($transaction_data['transaction_out_id'])):
                ?>
                        <span class="details-label">
                            <?= Security::htmlentities(_("Payment ID")); ?>:
                        </span>
                        <span class="details-value">
                            <?= $transaction_data['transaction_out_id']; ?>
                        </span>
                        <br>
                <?php
                    endif;
                    
                    if (!empty($transaction_data['payment_method_details_string'])) {
                        $include_path = APPPATH . "views/whitelabel/transactions/payment/" .
                            $transaction_data['payment_method_details_string'] . ".php";
                        if (file_exists($include_path)) {
                            include($include_path);
                        }
                    }

                    if (!empty($transaction_urls['tickets_view'])):
                ?>
                        <br>
                        <a href="<?= $transaction_urls['tickets_view']; ?>" 
                           class="btn btn-xs btn-primary">
                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                        </a>
                <?php
                    endif;
                ?>
            </div>
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
