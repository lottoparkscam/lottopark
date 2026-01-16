<?php
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/transactions/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Withdrawal details"); ?> <small><?= $withdrawal_data['full_token']; ?></small>
        </h2>
        <p class="help-block">
            <?= _("Here you can view the withdrawal details."); ?>
        </p>
        <a href="<?= $withdrawal_urls['back_to_list']; ?>" 
           class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <?php
                include(APPPATH . "views/whitelabel/shared/messages.php");
            ?>
            <div class="col-md-6 user-details">
                <span class="details-label">
                    <?= Security::htmlentities(_("ID")); ?>:
                </span>
                <span class="details-value">
                    <?= $withdrawal_data['full_token']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("User ID")); ?>:
                </span>
                <span class="details-value">
                    <?= $withdrawal_data['user_full_token']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("First Name")); ?>:
                </span>
                <span class="details-value">
                    <?= $withdrawal_data['first_name']; ?>
                </span>
                <br>
		<span class="details-label">
                    <?= Security::htmlentities(_("Last Name")); ?>:
                </span>
		<span class="details-value">
                    <?= $withdrawal_data['surname']; ?>
                </span>
                <br>
		<span class="details-label">
                    <?= Security::htmlentities(_("E-mail")); ?>:
                </span>
		<span class="details-value">
                    <?= $withdrawal_data['email']; ?>
                </span>
                <br>
		<span class="details-label">
                    <?= Security::htmlentities(_("Date")); ?>:
                </span>
		<span class="details-value">
                    <?= $withdrawal_data['date']; ?>
                </span>
                <br>
		<?php
                    if (!empty($withdrawal_data['date_confirmed'])):
                ?>
                        <span class="details-label">
                            <?= Security::htmlentities(_("Date approved")); ?>:
                        </span>
                        <span class="details-value">
                            <?= $withdrawal_data['date_confirmed']; ?>
                        </span>
                        <br>
		<?php
                    endif;
                ?>
		<span class="details-label">
                    <?= Security::htmlentities(_("Method")); ?>:
                </span>
		<span class="details-value">
                    <?= $withdrawal_data['method_name']; ?>
                </span>
                <br>
		<span class="details-label">
                    <?= Security::htmlentities(_("Amount")); ?>:
                </span>
		<span class="details-value">
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
                </span>
                <br>
		<span class="details-label">
                    <?= Security::htmlentities(_("User balance")); ?>:
                </span>
		<span class="details-value">
                    <span<?= $withdrawal_data['user_balance_class']; ?>>
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
                    </span>
		</span>
                <br>
		<span class="details-label">
                    <?= Security::htmlentities(_("Status")); ?>:
                </span>
		<span class="details-value">
                    <span class="<?= $withdrawal_data['status_show_class']; ?>">
                        <?= $withdrawal_data['status_text']; ?>
                    </span>
		</span>
                <br>
                
		<h3><?= _("Request details"); ?></h3>
		<span class="details-label">
            <?= Security::htmlentities(_("First Name")); ?>:
        </span>
		<span class="details-value">
            <?= $withdrawal_data['data_first_name']; ?>
        </span>
        <br>
		<span class="details-label">
            <?= Security::htmlentities(_("Last Name")); ?>:
        </span>
		<span class="details-value">
            <?= $withdrawal_data['data_surname']; ?>
        </span>
        <br>
		<?php
                    if (isset($withdrawal_data['data'])):
                        foreach ($withdrawal_data['data'] as $single_row):
                ?>
                            <span class="details-label">
                                <?= $single_row['label']; ?>:
                            </span>
                            <span class="details-value">
                                <?= $single_row['value']; ?>
                            </span>
                            <br>
                <?php
                        endforeach;
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
