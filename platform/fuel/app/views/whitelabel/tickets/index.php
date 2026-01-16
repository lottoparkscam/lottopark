<?php

use Models\Whitelabel;

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
            <?= _("Tickets"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can view and manage users' tickets."); ?>
        </p>
        <?php
            include(APPPATH . "views/whitelabel/tickets/index_filters.php");
        ?>
		<div class="container-fluid container-admin">
            <?php
                include(APPPATH . "views/whitelabel/shared/messages.php");

                if (isset($tickets_data) && count($tickets_data) > 0):
                    $default_currency_code = Helpers_Currency::get_default_currency_code();
                    echo $pages;
            ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("ID"); ?>
                                        <br>
                                        <?= _("Transaction ID"); ?>
                                        <br>
                                        <?= _("Lottery"); ?>
                                    </th>
                                    <th>
                                        <?= _("User ID &bull; User Name"); ?>
                                        <br>
                                        <?= _("E-mail"); ?>
                                    </th>
                                    <th>
                                        <?= _("Pricing"); ?>
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
                                    <th class="text-center tablesorter-header tablesorter-<?= $sort['draw_date']['class']; ?>" 
                                        data-href="<?= $sort['draw_date']['link']; ?>">
                                        <?= _("Draw Date"); ?>
                                    </th>
                                    <th>
                                        <?= _("Status"); ?>
                                    </th>
                                    <th class="tablesorter-header tablesorter-<?= $sort['prize']['class']; ?>" 
                                        data-href="<?= $sort['prize']['link']; ?>">
                                        <?= _("Prize"); ?>
                                    </th>
                                    <th>
                                        <?= _("Paid out"); ?>
                                    </th>
                                    <th>
                                        <?= _("Lines"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Manage"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach ($tickets_data as $ticket_data):
                                ?>
                                        <tr>
                                            <td>
                                                <?= $ticket_data['ticket_token']; ?>
                                                <br>
                                                <?= $ticket_data['transaction_token']; ?>
                                                <br>
                                                <?= $ticket_data['lottery_name']; ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <?php
                                                    echo $ticket_data['user_token'];
                                                    echo " &bull; ";
                                                    echo $ticket_data['user_fullname'];
                                                    echo "<br>";
                                                    echo $ticket_data['user_email'];
                                                    echo "<br>";
                                                    /** @var Whitelabel $whitelabelModel */
                                                    $whitelabelModel = Container::get('whitelabel');
                                                    if ($whitelabelModel->loginForUserIsUsedDuringRegistration()) {
                                                        echo $ticket_data['user_login'];
                                                        echo "<br>";
                                                    }

                                                    if ($ticket_data['show_deleted']):
                                                ?>
                                                        <span class="text-danger">
                                                            <?= _("Deleted"); ?>
                                                        </span>
                                                        <br>
                                                <?php
                                                    endif;
                                                ?>
                                                    <a href="<?= $ticket_data['show_user_url']; ?>" 
                                                       class="btn btn-xs btn-primary">
                                                        <span class="glyphicon glyphicon-user"></span> 
                                                        <?= _("View user"); ?>
                                                    </a>    
                                                <?php
                                                    if (!empty($ticket_data['whitelabel_transaction_id'])):
                                                        echo "<br>";
                                                ?>
                                                        <a href="<?= $ticket_data['transaction_url']; ?>" 
                                                               class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> 
                                                            <?= _("View transaction"); ?>
                                                        </a>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?= _("Model"); ?>: 
                                                <?= $ticket_data['model_name']; ?>
                                                <?= $ticket_data['tier']; ?>
                                                <br>
                                                <?= _("Cost"); ?>: 
                                                <?php
                                                    echo $ticket_data['cost_manager'];

                                                    if (!empty($ticket_data['costs_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?= $ticket_data['costs_other']; ?>">
                                                            </span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                                <br>
                                                <?= _("Income"); ?>: 
                                                <?php
                                                    echo $ticket_data['income_manager'];

                                                    if (!empty($ticket_data['incomes_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?= $ticket_data['incomes_other']; ?>">
                                                            </span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                                <br>
                                                <?= _("Royalties"); ?>: 
                                                <?php
                                                    echo $ticket_data['margin_manager'];

                                                    if (!empty($ticket_data['margins_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?= $ticket_data['margins_other']; ?>">
                                                            </span>
                                                        </small>
                                                <?php
                                                    endif;
                                                    
                                                    if (!empty($ticket_data['bonus_cost_manager'])):
                                                ?>
                                                        <br>
                                                        <?= _("Bonus") ?>:
                                                        <?php
                                                            echo $ticket_data['bonus_cost_manager'];

                                                            if (!empty($ticket_data['bonus_cost_other'])):
                                                        ?>
                                                                <small>
                                                                    <span class="glyphicon glyphicon-info-sign" 
                                                                          data-toggle="tooltip" 
                                                                          data-placement="top" 
                                                                          title="" 
                                                                          data-original-title="<?= $ticket_data['bonus_cost_other']; ?>">
                                                                    </span>
                                                                </small>
                                                <?php
                                                            endif;
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    echo $ticket_data['amount_manager'];
                                                    
                                                    if (!empty($ticket_data['amounts_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?= $ticket_data['amounts_other']; ?>">
                                                            </span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    echo $ticket_data['bonus_amount_manager'];
                                                    
                                                    if (!empty($ticket_data['bonus_amounts_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?= $ticket_data['bonus_amounts_other']; ?>">
                                                            </span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $ticket_data['date']; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                    if (!empty($ticket_data['draw_date'])):
                                                        echo $ticket_data['draw_date'];
                                                    elseif (!empty($ticket_data['valid_to_draw'])):
                                                ?>
                                                        <span>
                                                            <?= _("Valid to"); ?>:
                                                        </span>
                                                        <br>
                                                <?php
                                                        echo $ticket_data['valid_to_draw'];
                                                    endif;
                                                ?>
                                            </td>

                                            <td>
                                                <span class="<?= $ticket_data['status_class']; ?>">
                                                    <?= $ticket_data['status_text']; ?>
                                                </span>
                                                <?php
                                                    if (!empty($ticket_data['status_extra_text'])):
                                                        echo "<br>";
                                                ?>
                                                        <span class="<?= $ticket_data['status_extra_class']; ?>">
                                                            <?= $ticket_data['status_extra_text']; ?>
                                                        </span>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                    if ($ticket_data['status_win']):
                                                        echo $ticket_data['jackpot_prize_text'];

                                                        echo $ticket_data['prize_manager'];

                                                        if (!empty($ticket_data['prizes_other'])):
                                                ?>
                                                            <small>
                                                                <span class="glyphicon glyphicon-info-sign" 
                                                                      data-toggle="tooltip" 
                                                                      data-placement="top" 
                                                                      title="" 
                                                                      data-original-title="<?= $ticket_data['prizes_other']; ?>">
                                                                </span>
                                                            </small>
                                                <?php
                                                        endif;

                                                        if (!empty($ticket_data['prize_net_manager'])):
                                                ?>
                                                            <span>
                                                                <?= Security::htmlentities(_("Net")); ?>:
                                                            </span> 
                                                <?php
                                                            echo $ticket_data['prize_net_manager'];

                                                            if (!empty($ticket_data['prize_net_local'])):
                                                ?>
                                                                <small>
                                                                    <span class="glyphicon glyphicon-info-sign" 
                                                                          data-toggle="tooltip" 
                                                                          data-placement="top" 
                                                                          title="" 
                                                                          data-original-title="<?= $ticket_data['prize_net_local']; ?>">
                                                                    </span>
                                                                </small>
                                                <?php
                                                            endif;
                                                        endif;

                                                        echo $ticket_data['prize_quickpick'];
                                                    endif;
                                                ?>
                                            </td>
                                            <td<?= $ticket_data['payout_class']; ?>>
                                                <?= $ticket_data['payout']; ?>
                                            </td>
                                            <td>
                                                <?= $ticket_data['count']; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= $ticket_data['details_url']; ?>"
                                                   class="btn btn-xs btn-primary">
                                                    <span class="glyphicon glyphicon-list"></span>
                                                    <?= _("Details"); ?>
                                                </a>
                                                <?php if (!empty($ticket_data['multidraw_token'])): ?>
                                                    <br/>
                                                    <a href="<?= $ticket_data['multidraw_url']; ?>"
                                                       class="btn btn-xs btn-warning">
                                                        <span class="glyphicon glyphicon-list"></span>
                                                        <?= _("View Multi-draw"); ?>
                                                    </a>
                                                <?php endif; ?>
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
                        <?= _("No tickets."); ?>
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
