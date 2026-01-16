<?php

use Models\Whitelabel;

include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/tickets/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Ticket details"); ?> <small><?= $ticket_data['ticket_token']; ?></small>
        </h2>
        <p class="help-block">
            <?= _("Here you can view ticket details."); ?>
        </p>
        <a href="/tickets<?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
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
                    <?= $ticket_data['ticket_token']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Transaction ID")); ?>:
                </span>
                <span class="details-value">
                    <?= $ticket_data['transaction_token']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("User ID")); ?>:
                </span>
                <span class="details-value">
                    <?= $ticket_data['user_token']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("First Name")); ?>:
                </span>
                <span class="details-value">
                    <?= $ticket_data['user_name']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Last Name")); ?>:
                </span>
                <span class="details-value">
                    <?= $ticket_data['user_surname']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("E-mail")); ?>:
                </span>
                <span class="details-value">
                    <?= $ticket_data['user_email']; ?>
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
                            <?= $ticket_data['user_login']; ?>
                        </span>
                    <br>
                <?php
                endif
                ?>
                <span class="details-label">
                    <?= Security::htmlentities(_("Lottery")); ?>:
                </span>
                <span class="details-value">
                    <?= $ticket_data['lottery_name']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Date")); ?>:
                </span>
                <span class="details-value">
                    <?= $ticket_data['date']; ?>
                </span>
                <br>
                <?php
                if (!empty($ticket_data['draw_date'])):
                    ?>
                    <span class="details-label">
                            <?= Security::htmlentities(_("Draw Date")); ?>:
                        </span>
                    <span class="details-value">
                            <?= $ticket_data['draw_date']; ?>
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
				</span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Bonus amount")); ?>:
                </span>
                <span class="details-value">
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
				</span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Model")); ?>:
                </span>
                <span class="details-value">
                    <?= $ticket_data['model_name']; ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Cost")); ?>:
                </span>
                <span class="details-value">
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
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Income")); ?>:
                </span>
                <span class="details-value">
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
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Royalties")); ?>:
                </span>
                <span class="details-value">
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
                    ?>
                </span>
                <?php
                if (!empty($ticket_data['bonus_cost_manager'])):
                    ?>
                    <br>
                    <span class="details-label">
                            <?= Security::htmlentities(_("Bonus")); ?>:
                        </span>
                    <span class="details-value">
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
                            ?>
                        </span>
                <?php
                endif;
                ?>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Status")); ?>:
                </span>
                <span class="details-value">
                    <span class="<?= $ticket_data['status_class']; ?>">
                        <?= $ticket_data['status_text']; ?>
                    </span>
                    <?php
                    if (!empty($ticket_data['status_extra_text'])):
                        ?>
                        <span class="<?= $ticket_data['status_extra_class']; ?>">
                                <?= $ticket_data['status_extra_text']; ?>
                            </span>
                    <?php
                    endif;
                    ?>
				</span>
                <br>
                <span class="details-label">
                    <?= _("Paid out"); ?>:
                </span>
                <span class="details-value">
                    <span class="<?= $ticket_data['payout_class']; ?>">
                        <?= $ticket_data['payout_text']; ?>
                    </span>
                </span>
                <?php if (isset($ticket_data['numbers_per_line'])): ?>
                    <br>
                    <span class="details-label">
                    <?= _("Numbers per line"); ?>:
                </span>
                    <span class="details-value">
                        <?= $ticket_data['numbers_per_line']; ?>
                </span>
                <?php endif; ?>
                <?php if (isset($ticket_data['ticket_multiplier'])): ?>
                    <br>
                    <span class="details-label">
                    <?= _("Ticket multiplier"); ?>:
                </span>
                    <span class="details-value">
                        <?= $ticket_data['ticket_multiplier']; ?>
                </span>
                <?php endif; ?>
                <?php
                include(APPPATH . "views/whitelabel/tickets/view_lines.php");

                if ($ticket_data['status_win']):
                    ?>
                    <h3>
                        <?= Security::htmlentities(_("Total Prize")); ?>
                    </h3>
                    <?php
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
                if ($whitelabelModel->isScansDisplayedForUsers && $images !== null && count($images) > 0):
                    ?>
                    <h3>
                        <?= _("Slip images"); ?>
                    </h3>
                    <?php
                    foreach ($images as $image):
                        ?>
                        <img src="<?= $image; ?>" alt="" class="img-thumbnail">
                    <?php
                    endforeach;
                    ?>
                    <div class="clearfix"></div>
                <?php
                endif;

                if (!$ticket_data['is_payout']):
                    $payout_button_confirm_text = _(
                        "Are you sure? The 'mark as paid out' function will not " .
                        "automatically pay out the lines to the user and will not " .
                        "increase user balance. It will only mark the tickets and " .
                        "lines as paid out. Also, the Quick Pick lines will not be " .
                        "marked as paid out as they are paid out automatically after " .
                        "some time. This function is intended to be used only with " .
                        "bigger prizes which are not automatically paid out. " .
                        "If you want to pay out the lines to the user balance please " .
                        "use 'Pay out to user balance' button within specific line."
                    );
                    ?>
                    <div class="clearfix"></div>
                    <br>
                    <button type="button"
                            data-href="<?= $ticket_data['payout_button_url']; ?>"
                            class="btn btn-success"
                            data-toggle="modal"
                            data-target="#confirmModal"
                            data-confirm="<?= $payout_button_confirm_text; ?>">
                        <span class="glyphicon glyphicon-ok"></span>
                        <?php
                        echo "&nbsp;";
                        echo _("Mark as paid out");
                        ?>
                    </button>
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
