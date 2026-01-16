<?php

use Carbon\Carbon;
use Helpers\ImageHelper;
use Helpers\UrlHelper;

$lottery_additional_data = null;

if (isset($draw)) {
    $lottery_additional_data = unserialize($draw['additional_data']);
    if ($lottery_additional_data === false) {
        $lottery_additional_data = null;
    }
}

$action_var = get_query_var("action");

if (isset($action) && $action == "details") :
    if (isset($ticket) && (int)$ticket['status'] !== Helpers_General::TICKET_STATUS_QUICK_PICK) :
        $lottery = $lotteries['__by_id'][$ticket->lottery_id];
        $lottery_type = Model_Lottery_Type::get_lottery_type_for_date($lottery, $ticket->draw_date);
?>

        <hr class="separator">
        <div class="myaccount-transactions">
            <?php
            if ($isScansDisplayedForUsers):
            if (!empty($images) && count($images) > 0 && $ticket->lottery_id != 9): // we temporarily do not want to display scans for Bonoloto
            ?>
                <div class="myaccount-scan-container lotto-lightbox" data-label="<?= _("Image %1 of %2") ?>">
                    <?php
                    if (count($images) > 1) :
                    ?>
                        <a href="#" class="myaccount-scan-prev myaccount-scan-btn-disabled"><i class="fa fa-arrow-circle-left"></i></a>
                        <a href="#" class="myaccount-scan-next"><i class="fa fa-arrow-circle-right"></i></a>
                    <?php
                    endif;
                    ?>
                    <div class="myaccount-scan-page">
                        <?php
                        $scan_page_text = "";
                        if (count($images) > 1) {
                            $scan_page_text = "<span>1</span>/";
                            $scan_page_text .= count($images);
                        } else {
                            $scan_page_text = "&nbsp;";
                        }
                        echo $scan_page_text;
                        ?>
                    </div>
                    <?php
                    foreach ($images as $key => $image) :
                        $class_div = '';
                        if ((int)$key !== 0) {
                            $class_div = ' hidden-normal';
                        }
                        $img_url = site_url('/') . "account/slip/" . $image . "/";
                        $isImageBase64Encoded = ImageHelper::isImageBase64Encoded($image);
                        if ($isImageBase64Encoded) {
                            $img_url = $image;
                        }
                    ?>
                        <div class="myaccount-scan<?= $class_div; ?>">
                            <a href="<?= $img_url; ?>" data-lightbox="slip">
                                <img src="<?= $img_url; ?>" alt="<?= $image; ?>">
                            </a>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
            <?php
            elseif ((int)$ticket->has_ticket_scan === 1) :
            ?>
                <div class="myaccount-scan-container lotto-lightbox">
                    <div class="myaccount-scan">
                        <img src="<?= get_template_directory_uri(); ?>/images/scans-in-progress.png" alt="Scans in progress">
                    </div>
                </div>
            <?php
            endif;
            endif;
            ?>

            <?php
            $lottery_image = Lotto_View::get_lottery_image($lottery['id']);

            if ((int)$ticket['status'] !== Helpers_General::TICKET_STATUS_PENDING) {
                $draw = Model_Lottery_Draw::getLotteryDrawByLotteryIdAndTicketDrawDate($lottery['id'], $ticket->draw_date);
            }

            $lottery_name = Security::htmlentities(_($lottery['name']));

            $lottery_url = lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']);

            $ticket_status_class = "transactions-status transactions-status-";
            $ticket_status_class .= htmlspecialchars($ticket['status']);

            $ticket_status_text = "";
            switch ($ticket['status']) {
                case Helpers_General::TICKET_STATUS_PENDING:
                    $ticket_status_text = $ticket['paid'] == 1 ? _("purchased") : _("unpaid");
                    break;
                case Helpers_General::TICKET_STATUS_WIN:
                    $ticket_status_text = _("win");
                    break;
                case Helpers_General::TICKET_STATUS_NO_WINNINGS:
                    $ticket_status_text = _("no winnings");
                    break;
                case Helpers_General::TICKET_STATUS_CANCELED:
                    $ticket_status_text = _("cancelled");
                    break;
            }

            $ticket_date = Helpers_View_Date::format_date_for_user_timezone($ticket->date);
            $ticket_date_text = Security::htmlentities($ticket_date);
            ?>
            <div class="pull-left myaccount-details-image">
                <img src="<?= UrlHelper::esc_url($lottery_image); ?>" alt="<?= $lottery_name; ?>">
            </div>
            <div class="pull-left">
                <span class="myaccount-transactions-label">
                    <?= _("Lottery") ?>:
                </span>
                <span class="myaccount-transactions-value tickets-lottery-name">
                    <a href="<?= UrlHelper::esc_url($lottery_url); ?>">
                        <?= $lottery_name; ?>
                    </a>
                </span>
                <br>
                <span class="myaccount-transactions-label">
                    <?= _("Status") ?>:
                </span>
                <span class="myaccount-transactions-value">
                    <span class="<?= $ticket_status_class; ?>">
                        <?= $ticket_status_text; ?>
                    </span>
                </span>
                <br>
                <span class="myaccount-transactions-label">
                    <?= _("Draw number") ?>:
                </span>
                <span class="myaccount-transactions-value">
                    <?= isset($draw['draw_no']) ? $draw['draw_no'] : ''; ?>
                </span><br>
                <span class="myaccount-transactions-label">
                    <?= _("Date") ?>:
                </span>
                <span class="myaccount-transactions-value">
                    <?= $ticket_date_text; ?>
                </span>
                <br>
                <span class="myaccount-transactions-label">
                    <?= _("Ticket type") ?>:
                </span>
                <span class="myaccount-transactions-value">
                    <?php if (!empty($multi_draws)) : ?>
                        <?= _("Multi draw") ?>
                    <?php else : ?>
                        <?= _("Single draw") ?>
                    <?php endif; ?>
                </span>
                <?php if (isset($ticket_multiplier)) : ?>
                    <br>
                    <span class="myaccount-transactions-label">
                        <?= _("Multiplier") ?>:
                    </span>
                    <span class="myaccount-transactions-value">
                        <?= sprintf("x%s", $ticket_multiplier); ?>
                    </span>
                <?php endif; ?>
                <br>
                <?php
                if (!empty($ticket->date_processed)) :
                    $ticket_date_processed = Helpers_View_Date::format_date_for_user_timezone($ticket->date_processed);
                    $ticket_date_processed_text = Security::htmlentities($ticket_date_processed);
                ?>
                    <span class="myaccount-transactions-label">
                        <?= _("Date processed") ?>:
                    </span>
                    <span class="myaccount-transactions-value">
                        <?= $ticket_date_processed_text; ?>
                    </span>
                    <br>
                <?php
                endif;

                $draw_date = Helpers_View_Date::format_date_for_user_timezone($ticket->draw_date, $lottery['timezone']);
                $draw_date_options = [
                    "span" => [
                        "class" => [],
                        "data-tooltip" => []
                    ],
                    "strong" => []
                ];
                $draw_date_text = wp_kses($draw_date, $draw_date_options);

                $amount_field = $ticket->amount;
                $payment_type_is_bonus_balance = isset($transaction) &&
                    (int)$transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE;

                if ($payment_type_is_bonus_balance) {
                    $amount_field = $ticket->bonus_amount;
                }

                $amount = "";
                if ($ticket->amount == 0 && $ticket->bonus_amount == 0) {
                    $amount = _("Free");
                } else {
                    $amount = Lotto_View::format_currency(
                        $amount_field,
                        $currencies[$ticket['currency_id']]['code'],
                        true
                    );
                }
                $amount_text = Security::htmlentities($amount);
                ?>
                <span class="myaccount-transactions-label">
                    <?= _("Draw date") ?>:
                </span>
                <span class="myaccount-transactions-value myaccount-transaction-value-time">
                    <?= $draw_date_text; ?>
                </span>
                <br>
                <span class="myaccount-transactions-label">
                    <?= _("Amount") ?>:
                </span>
                <span class="myaccount-transactions-value">
                    <span class="transactions-amount">
                        <?= $amount_text; ?>
                    </span>
                    <?php if ($payment_type_is_bonus_balance) : ?>
                        <span class="info-circle fa fa-info-circle tooltip tooltip-bottom" data-tooltip="<?= _("Paid with bonus balance.") ?>">
                        </span>
                    <?php endif; ?>
                </span>

                <?php
                if (!empty($ticket->whitelabel_transaction_id) && isset($transaction)) :
                    $transaction_url = UrlHelper::esc_url($transaction_link . 'details/' . $transaction->token);

                    $transaction_full_id = $whitelabel['prefix'];
                    if ($transaction->type == Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                        $transaction_full_id .= 'P';
                    } else {
                        $transaction_full_id .= 'D';
                    }
                    $transaction_full_id .= $transaction->token;
                    $transaction_token_text = Security::htmlentities($transaction_full_id);
                ?>
                    <br>
                    <span class="myaccount-transactions-label">
                        <?= _("Transaction ID") ?>:
                    </span>
                    <span class="myaccount-transactions-value">
                        <a href="<?= UrlHelper::esc_url($transaction_url); ?>">
                            <?= $transaction_token_text; ?>
                        </a>
                    </span>
                <?php
                endif;

                $isKenoAndTicketStatusPending = (int)$ticket->status === Helpers_General::TICKET_STATUS_PENDING && $lottery['type'] === Helpers_Lottery::TYPE_KENO;
                if ($isKenoAndTicketStatusPending) :
                    $userTimezone = get_user_timezone();
                    $nextDraw = Lotto_Helper::get_lottery_real_next_draw($lottery);
                    $nextDraw->setTimezone($userTimezone ?? 'UTC');
                    $nextDrawTimestamp = $nextDraw->getTimestamp();
                    $nextDraw = Carbon::createFromTimestamp($nextDrawTimestamp);
                    $now = Carbon::now($userTimezone);
                    $drawDateDiff = $now->diff($nextDraw);
                    $minNumberWithoutLeadingZero = 10;
                ?>
                    <time datetime="<?= $nextDrawTimestamp; ?>" class="widget-ticket-time-remain countdown keno-countdown details" id="widget-ticket-time-remain-desktop" repeatMoreThanOnce="false">
                        <span class="draw-in-text"><?= _('Draw in: ') ?> </span>
                        <div class="timer">
                            <hr>
                            <div>
                                <span class="time hours" style="display: none">
                                    <span class="loading loading-big"></span>
                                    <span style="display: none">
                                        <?= str_pad($drawDateDiff->h, 2, '0', STR_PAD_LEFT) ?>
                                    </span>
                                </span>
                                <span class="separator-hours" style="display: none">:</span>
                                <span class="time minutes"><?= $drawDateDiff->i < $minNumberWithoutLeadingZero ? '0' . $drawDateDiff->i : $drawDateDiff->i ?></span> : <span class="time seconds"><?= $drawDateDiff->s < $minNumberWithoutLeadingZero ? '0' . $drawDateDiff->s : $drawDateDiff->s ?></span>
                            </div>
                        </div>
                    </time>
                <?php
                endif;
                if ((int)$ticket->status === Helpers_General::TICKET_STATUS_WIN) :
                    $ticket_payout_class = "transactions-status transactions-status-";
                    $ticket_payout_class .= htmlspecialchars($ticket['payout']);

                    $ticket_payout_text = "";
                    switch ($ticket['payout']) {
                        case Helpers_General::TICKET_PAYOUT_PENDING:
                            $ticket_payout_text = _("pending");
                            break;
                        case Helpers_General::TICKET_PAYOUT_PAIDOUT:
                            $ticket_payout_text = _("paid out");
                            break;
                    }
                ?>
                    <br>
                    <span class="myaccount-transactions-label">
                        <?= _("Pay out") ?>:
                    </span>
                    <span class="myaccount-transactions-value">
                        <span class="<?= $ticket_payout_class; ?>">
                            <?= $ticket_payout_text; ?>
                        </span>
                    </span>
                <?php
                endif;
                ?>
            </div>
        </div>

        <div class="clearfix"></div>
        <hr class="separator">
    <?php else : ?>
        <?php include 'raffle-myaccount_tickets_details.php'; ?>
        <?php endif;

    if (isset($lines) && count($lines) > 0) :
        if (isset($draw)) :
            $date_local_full = $draw->date_local;
            $date_local = Helpers_View_Date::format_date_for_user_timezone($date_local_full, $lottery['timezone']);
            $date_local_final = Security::htmlentities($date_local);

            $line_data = Lotto_View::format_line(
                $draw['numbers'],
                $draw['bnumbers'],
                null,
                null,
                null,
                $lottery_additional_data
            );
        ?>
            <div class="account-tickets">
                <?= _("Draw result") ?>
            </div>
            <div class="account-tickets-draw-date">
                <?= $date_local_final; ?>
            </div>
            <div class="tickets-lines">
                <?= $line_data; ?>
            </div>
        <?php
        endif;
        ?>
        <div class="account-tickets">
            <?= _("Ticket details") ?>
        </div>
        <div class="tickets-lines-counter">
            <?= Security::htmlentities(sprintf(_("Lines: %s"), count($lines))); ?>
        </div>
        <div class="tickets-lines">
            <?php
            $jackpot = false;
            $slip_additional_data = null;

            $quickpick = 0;
            foreach ($lines as $line):
                echo '<div class="tickets-line-row">';
                if (isset($line['additional_data'])) {
                    $slip_additional_data = unserialize($line['additional_data']);
                    if ($slip_additional_data === false) {
                        $slip_additional_data = null;
                    }
                }

                if (!isset($draw)) :
                    echo Lotto_View::format_line(
                        $line['numbers'],
                        $line['bnumbers'],
                        null,
                        null,
                        null,
                        $slip_additional_data
                    );
            ?>
                    <br>
                <?php
                else :
                    echo Lotto_View::format_line(
                        $line['numbers'],
                        $line['bnumbers'],
                        $draw['numbers'],
                        $draw['bnumbers'],
                        $lottery_type['bextra'],
                        $slip_additional_data,
                        $lottery_additional_data
                    );
                ?>
                    <span class="myaccount-tickets-match-data">
                        <strong><?= _("Match") ?>:</strong>
                        <?php
                        if (isset($line['lottery_type_data_id'])) {
                            if (Helpers_Lottery::is_keno($lottery)) {
                                echo Security::htmlentities(sprintf("%s / %s", $line['match_b'], $line['match_n']));
                            } else {
                                echo Security::htmlentities($line['match_n'] . ($lottery_type['brange'] ? ' + ' . $line['match_b'] : ($lottery_type['bextra'] && $line['match_b'] ? " + {$line['match_b']}" : '')));

                                if (!empty($lottery_additional_data) && !empty($slip_additional_data)) {
                                    if ($slip_additional_data == $lottery_additional_data) {
                                        echo " + ";
                                        //                                           //TODO: refactor
                                        $lottery_type_additional_data = unserialize($lottery_type['additional_data']);
                                        $ball_shortname = '';
                                        if (isset($lottery_type_additional_data['super'])) {
                                            $ball_shortname = 'S';
                                        }
                                        if (isset($lottery_type_additional_data['refund'])) {
                                            $ball_shortname = 'R';
                                        }
                                        echo Security::htmlentities(_($ball_shortname));
                                    }
                                }
                            }
                        } else {
                            echo _("none");
                        }

                        if (isset($line['lottery_type_data_id'])) :
                        ?>
                            <strong><?= _("Prize") ?>:</strong>
                            <span class="transactions-amount">
                                <?php
                                if ($line['is_jackpot']) :
                                    $jackpot = true;
                                    echo _("Jackpot");
                                elseif ($line['type'] == 2) :
                                    $quickpick++;
                                    echo _("Quick Pick");
                                else :
                                    echo Security::htmlentities(Lotto_View::format_currency($line['prize_local'], $currencies[$lottery['currency_id']]['code'], true));

                                    if ($line['prize_local'] != $line['prize_net_local']) :
                                ?>
                            </span><strong><?php
                                            echo _("Net");
                                            ?>:</strong><span class="transactions-amount"> <?php
                                                                                echo Security::htmlentities(Lotto_View::format_currency($line['prize_net_local'], $currencies[$lottery['currency_id']]['code'], true));
                                                                                ?>
                        <?php
                                    endif;

                                    if ((int)$lottery['currency_id'] !== (int)$ticket['currency_id']) :
                                        echo "(" . Security::htmlentities(Lotto_View::format_currency($line['prize_net'], $currencies[$ticket['currency_id']]['code'], true)) . ")";
                                    endif;
                                endif;
                        ?>
                            </span>
                            <strong><?= _("Status") ?>:</strong>
                            <span class="transactions-status transactions-status-<?php
                                                                                    echo htmlspecialchars($line['payout']);
                                                                                    ?>"><?php
                            switch ($line['payout']):
                                case Helpers_General::TICKET_PAYOUT_PENDING:
                                    echo _("pending");
                                    break;
                                case Helpers_General::TICKET_PAYOUT_PAIDOUT:
                                    echo _("paid out");
                                    break;
                            endswitch;
                            ?>
                            </span></span>
                    <?php else:?>
                        <strong><?= _("Status") ?>:</strong>
                        <span class="transactions-status transactions-status-2"><?= _("no winnings") ?></span>
                </span>
                <?php
                        endif;
                ?>
                </span>
        <?php
                endif;
                echo '</div>';
            endforeach;
        ?>
        </div>

        <div class="clearfix"></div>

        <?php
        if (isset($draw)) :
        ?>
            <hr class="separator prize-separator">

            <div class="ticket-prize">
                <?php
                echo _("Prize");
                // TODO: There is something wrong with SPAN tag, I really don't know
                // how to resolve that!
                ?>:<span>
                    <?php
                    if ((int)$ticket['status'] === Helpers_General::TICKET_STATUS_WIN) :
                        if ($jackpot) {
                            echo _("Jackpot");
                            if ($ticket['prize_net'] > 0) {
                                echo " + ";
                            }
                        }

                        if ($ticket['prize_net'] > 0) :
                            echo Security::htmlentities(Lotto_View::format_currency($ticket['prize_local'], $currencies[$lottery['currency_id']]['code'], true));

                            if ($ticket['prize_local'] != $ticket['prize_net_local']) :
                    ?>
                </span><?php
                                echo _("Net");
                        ?>:<span> <?php
                                                echo Security::htmlentities(Lotto_View::format_currency($ticket['prize_net_local'], $currencies[$lottery['currency_id']]['code'], true));
                                            endif;

                                            if ($lottery['currency_id'] != $ticket['currency_id']) :
                                                ?>
                    (<?php
                                                echo Security::htmlentities(Lotto_View::format_currency($ticket['prize_net'], $currencies[$ticket['currency_id']]['code'], true));
                        ?>)
        <?php
                                            endif;
                                        endif;

                                        if ($quickpick) :
                                            if ($ticket['prize_net'] > 0 || $jackpot) {
                                                echo " + ";
                                            }

                                            echo $quickpick;
                                            echo "&times;";
                                            echo _("Quick Pick");
                                        endif;
                                    else :
                                        echo _("-");
                                    endif;
        ?>
                </span>
            </div>
    <?php
        endif;
    endif;
    ?>
    <div id="return-to-ticket-list-div">
        <?php
        $isAwaiting = (int)$ticket['status'] === Helpers_General::TICKET_STATUS_PENDING;
        ?>
        <a href="<?= UrlHelper::esc_url($tickets_link . ($isAwaiting ? 'awaiting' : '') . Lotto_View::query_vars()); ?>" class="btn btn-primary btn-md">
            <?= _("Return to ticket list"); ?>
        </a>
    </div>
    <?php
else :
    echo lotto_platform_messages();

    if (!empty($this->errors) && count($this->errors) > 0) :
    ?>
        <div class="platform-alert platform-alert-error">
            <?php
            foreach ($this->errors as $error) :
                echo '<p><span class="fa fa-exclamation-circle"></span> ' . Security::htmlentities($error) . '</p>';
            endforeach;
            ?>
        </div>
    <?php
    endif;

    if ($count_past > 0 || $count_awaiting > 0) :
    ?>

        <!--TICKET MENU-->
        <div class="myaccount-tickets-menu">
            <a href="<?= lotto_platform_get_permalink_by_slug('account') . 'tickets/'; ?>" class="myaccount-tickets-menu-item<?= ($action_var == "") ? ' active' : '' ?>">
                <?= _("Past Tickets") ?> <span>(<?= $count_past; ?>)</span>
            </a>

            <a href="<?= lotto_platform_get_permalink_by_slug('account') . 'tickets/awaiting/'; ?>" class="myaccount-tickets-menu-item<?= ($action_var == "awaiting") ? ' active' : '' ?>">
                <?= _("Upcoming Draws") ?> <span>(<?= $count_awaiting; ?>)</span>
            </a>
        </div>

        <?php
        if (
            get_query_var("section") == 'tickets' &&
            empty(get_query_var("action"))
        ) :
        ?>
            <div class="myaccount-filter myaccount-filter-float-left">
                <form method="get" action=".">
                    <label for="myaccount-filter-select" class="table-sort-label hidden-normal">
                        <?php echo _("Show") ?>:
                    </label>
                    <select id="myaccount-filter-select" class="myaccount-filter-select myaccount-filter-select-float-left" name="filter[status]">
                        <option value="a" <?php if (Input::get("filter.status") == "a" || Input::get("filter.status") == null) :
                                                echo ' selected="selected"';
                                            endif; ?>>
                            <?= _("show all") ?>
                        </option>
                        <option value="1" <?php if (Input::get("filter.status") == "1") :
                                                echo ' selected="selected"';
                                            endif; ?>>
                            <?= _("show win") ?>
                        </option>
                        <option value="2" <?php if (Input::get("filter.status") == "2") :
                                                echo ' selected="selected"';
                                            endif; ?>>
                            <?= _("show no winnings") ?>
                        </option>
                    </select>
                </form>
            </div>
        <?php
        endif;
        ?>

        <div class="mobile-only-tickets pull-right">
            <label for="myaccount-tickets-mobile-sort" class="table-sort-label"><?= _("Sort by") ?>: </label>
            <select id="myaccount-tickets-mobile-sort" class="myaccount-tickets-mobile-sort">
                <option value="<?= UrlHelper::esc_url($sort['id']['link_a']); ?>" <?php if (Input::get("sort") == "id" && Input::get("sort_order") == "asc") :
                                                                                        echo ' selected="selected"';
                                                                                    endif; ?>><?= Security::htmlentities(_("Ticket ID")); ?>
                    - <?= _("by lowest") ?></option>
                <option value="<?= UrlHelper::esc_url($sort['id']['link_d']); ?>" <?php if ((Input::get("sort") == "id" && Input::get("sort_order") == "desc") || Input::get("sort") == null) :
                                                                                        echo ' selected="selected"';
                                                                                    endif; ?>><?= _("Ticket ID") ?>
                    - <?= _("by highest") ?></option>
                <option value="<?= UrlHelper::esc_url($sort['amount']['link_a']); ?>" <?php if (Input::get("sort") == "amount" && Input::get("sort_order") == "asc") :
                                                                                            echo ' selected="selected"';
                                                                                        endif; ?>><?= _("Amount") ?>
                    - <?= Security::htmlentities(_("by lowest")); ?></option>
                <option value="<?= UrlHelper::esc_url($sort['amount']['link_d']); ?>" <?php if (Input::get("sort") == "amount" && Input::get("sort_order") == "desc") :
                                                                                            echo ' selected="selected"';
                                                                                        endif; ?>><?= _("Amount") ?>
                    - <?= _("by highest") ?></option>
                <option value="<?= UrlHelper::esc_url($sort['draw_date']['link_a']); ?>" <?php if (Input::get("sort") == "draw_date" && Input::get("sort_order") == "asc") :
                                                                                                echo ' selected="selected"';
                                                                                            endif; ?>><?= _("Draw Date") ?>
                    - <?= _("by oldest") ?></option>
                <option value="<?= UrlHelper::esc_url($sort['draw_date']['link_d']); ?>" <?php if (Input::get("sort") == "draw_date" && Input::get("sort_order") == "desc") :
                                                                                                echo ' selected="selected"';
                                                                                            endif; ?>><?= _("Draw Date") ?>
                    - <?= _("by newest") ?></option>
                <option value="<?= UrlHelper::esc_url($sort['prize_local']['link_a']); ?>" <?php if (Input::get("sort") == "prize_local" && Input::get("sort_order") == "asc") :
                                                                                                echo ' selected="selected"';
                                                                                            endif; ?>><?= _("Prize") ?>
                    - <?= _("by lowest") ?></option>
                <option value="<?= UrlHelper::esc_url($sort['prize_local']['link_d']); ?>" <?php if (Input::get("sort") == "prize_local" && Input::get("sort_order") == "desc") :
                                                                                                echo ' selected="selected"';
                                                                                            endif; ?>><?= _("Prize") ?>
                    - <?= _("by highest") ?></option>
            </select>
        </div>

        <div class="clearfix"></div>

        <table class="table table-transactions table-tickets table-sort">
            <thead>
                <tr>
                    <th class="text-left tablesorter-header tablesorter-<?= htmlspecialchars($sort['id']['class']); ?>" data-href="<?= UrlHelper::esc_url($sort['id']['link']); ?>">
                        <?= _("Ticket ID and date") ?>
                    </th>
                    <th class="text-left">
                        <?= _("Lottery name") ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['amount']['class']); ?>" data-href="<?= UrlHelper::esc_url($sort['amount']['link']); ?>">
                        <?= _("Amount") ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['draw_date']['class']); ?>" data-href="<?= UrlHelper::esc_url($sort['draw_date']['link']); ?>">
                        <?= _("Draw Date") ?>
                    </th>
                    <th><?= _("Status") ?></th>
                    <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['prize_local']['class']); ?>" data-href="<?= UrlHelper::esc_url($sort['prize_local']['link']); ?>">
                        <?= _("Prize") ?>
                    </th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($tickets as $key => $ticket) :
                    $lottery = $lotteries["__by_id"][$ticket['lottery_id']];

                    $full_token = $whitelabel['prefix'] . 'T' . $ticket['token'];
                    $ticket_id = Security::htmlentities(sprintf("%s", $full_token));

                    $ticket_date = Helpers_View_Date::format_date_for_user_timezone($ticket['date'], $lottery['timezone']);
                    $ticket_date_text = Security::htmlentities($ticket_date);

                    $play_url = lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']);

                    $lottery_image = Lotto_View::get_lottery_image($lottery['id']);
                    $lottery_image_src = UrlHelper::esc_url($lottery_image);

                    $ticket_amount = "";
                    if ($ticket['amount'] == 0) {
                        $ticket_amount = _("Free");
                    } else {
                        $ticket_amount = Lotto_View::format_currency(
                            $ticket['amount'],
                            $currencies[$ticket['currency_id']]['code'],
                            true
                        );
                    }
                    $ticket_amount_text = Security::htmlentities($ticket_amount);

                    $draw_date_text = "";
                    if (!empty($ticket['draw_date'])) {
                        $full_draw_date = $ticket['draw_date'];
                        $draw_date = Helpers_View_Date::format_date_for_user_timezone(
                            $full_draw_date,
                            $lottery['timezone']
                        );
                        $draw_date_text = Security::htmlentities($draw_date);
                    } else {
                        if (!empty($ticket['valid_to_draw'])) {
                            $draw_date_text = "<span>";
                            $draw_date_text .= _("Valid to draw");
                            $draw_date_text .= "</span>";
                            $draw_date_text .= "<br>";

                            $full_valid_to_draw_date = $ticket['valid_to_draw'];
                            $valid_to_draw = Helpers_View_Date::format_date_for_user_timezone(
                                $full_valid_to_draw_date,
                                $lottery['timezone']
                            );
                            $valid_to_draw_text = Security::htmlentities($valid_to_draw);

                            $draw_date_text .= $valid_to_draw_text;
                        }
                    }

                    $transaction_status_class = "text-center transactions-status transactions-status-";
                    if (
                        (int)$ticket['status'] === Helpers_General::TICKET_STATUS_QUICK_PICK &&
                        $validto < $lnext
                    ) {
                        $transaction_status_class .= Helpers_General::TICKET_STATUS_NO_WINNINGS;
                    } else {
                        $transaction_status_class .= htmlspecialchars($ticket['status']);
                    }

                    $status_text = "";
                    switch ($ticket['status']) {
                        case Helpers_General::TICKET_STATUS_PENDING:
                            $status_text = _("purchased");
                            break;
                        case Helpers_General::TICKET_STATUS_WIN:
                            $status_text = _("win");
                            break;
                        case Helpers_General::TICKET_STATUS_NO_WINNINGS:
                            $status_text = _("no winnings");
                            break;
                        case Helpers_General::TICKET_STATUS_QUICK_PICK:
                            if (!Lotto_Helper::is_lottery_closed($lottery, $ticket['valid_to_draw'], $whitelabel)) :
                                $status_text = _("quick pick");
                            else :
                                $status_text = _("quick pick");
                            endif;
                            break;
                        case Helpers_General::TICKET_STATUS_CANCELED:
                            $status_text = _("cancelled");
                            break;
                    }

                    $prize_class = "transactions-amount text-center mobile-unbold";
                    if ((int)$ticket['status'] !== Helpers_General::TICKET_STATUS_WIN) {
                        $prize_class .= ' mobile-hide';
                    }

                    $prize_text = "";
                    if ((int)$ticket['status'] === Helpers_General::TICKET_STATUS_WIN) {
                        if ($ticket['prize_jackpot']) {
                            $prize_text = _("Jackpot");
                            $prize_text .= "<br>";
                        }

                        if ($ticket['prize_net'] > 0) {
                            $prize_net_local = Lotto_View::format_currency(
                                $ticket['prize_net_local'],
                                $currencies[$lottery['currency_id']]['code'],
                                true
                            );
                            $prize_text .= Security::htmlentities($prize_net_local);

                            if ((int)$lottery['currency_id'] !== (int)$ticket['currency_id']) {
                                $prize_text .= "(";

                                $prize_net = Lotto_View::format_currency(
                                    $ticket['prize_net'],
                                    $currencies[$ticket['currency_id']]['code'],
                                    true
                                );
                                $prize_text .= Security::htmlentities($prize_net);

                                $prize_text .= ")";
                            }
                        }

                        if (
                            $ticket['prize_net'] > 0 &&
                            $ticket['prize_quickpick']
                        ) {
                            $prize_text .= "<br>";
                        }

                        if (
                            isset($ticket['prize_quickpick']) &&
                            (int)$ticket['prize_quickpick'] > 0
                        ) {
                            $prize_text .= $ticket['prize_quickpick'];
                            $prize_text .= "&times;";
                            $prize_text .= _("Quick Pick");
                        }
                    } else {
                        $prize_text = '-';
                    }
                ?>
                    <tr>
                        <td class="transactions-id">
                            <span class="tickets-id"><?= $ticket_id; ?>
                                <br>
                            </span>
                            <span class="tickets-date"><span class="fa fa-clock-o" aria-hidden="true"></span> <?= $ticket_date_text; ?></span>
                        </td>
                        <td>
                            <span class="tickets-lottery">
                                <a href="<?= UrlHelper::esc_url($play_url) ?>">
                                    <img src="<?= $lottery_image_src; ?>" alt="<?= htmlspecialchars(_($lottery['name'])); ?>">
                                    <span class="tickets-lottery-name"><?= Security::htmlentities(_($lottery['name'])); ?></span>
                                </a>
                            </span>
                            <br>
                        </td>
                        <td class="text-center">
                            <span class="mobile-only-label">
                                <?= _("Amount") ?>:
                            </span>
                            <span class="transactions-amount">
                                <?= $ticket_amount_text; ?>
                            </span>
                        </td>
                        <td class="text-center transactions-date">
                            <span class="mobile-only-label">
                                <?= _("Draw Date") ?>:
                            </span>
                            <?= $draw_date_text; ?>
                        </td>
                        <td class="<?= $transaction_status_class; ?>">
                            <?= $status_text; ?>
                        </td>
                        <td class="<?= $prize_class; ?>">
                            <?= $prize_text; ?>
                        </td>

                        <td class="text-center transactions-details text-nowrap">
                            <?php
                            if (
                                $ticket['status'] != Helpers_General::TICKET_STATUS_PENDING &&
                                $lottery['is_enabled'] &&
                                $lottery['is_temporarily_disabled'] == 0 &&
                                $lottery['playable'] == 1
                            ) :
                                $play_again_string = $tickets_link .
                                    'playagain/' .
                                    $ticket['token'] .
                                    '/' .
                                    Lotto_View::query_vars();
                                $play_again_url = UrlHelper::esc_url($play_again_string);
                            ?>
                                <a href="<?= UrlHelper::esc_url($play_again_url) ?>" class="tooltip tooltip-bottom" data-tooltip="<?= _("Play again") ?>">
                                    <span class="fa fa-refresh"></span>
                                </a>
                            <?php
                            else :
                            ?>
                                <a class="disabled"><span class="fa fa-refresh"></span></a>
                            <?php
                            endif;

                            $details_string = $tickets_link .
                                'details/' .
                                $ticket['token'] .
                                '/' .
                                Lotto_View::query_vars();
                            $details_url = esc_url($details_string);
                            ?>
                            <a href="<?= UrlHelper::esc_url($details_url) ?>" class="tooltip tooltip-bottom" data-tooltip="<?= _("Details") ?>">
                                <span class="fa fa-search"></span>
                            </a>
                            <?php
                            /* endif; */
                            ?>
                        </td>
                    </tr>
                <?php
                endforeach;
                ?>
            </tbody>
        </table>
    <?php

        include('myaccount_pagination.php');
    else :
    ?>
        <p><?= _("No tickets.") ?></p>
<?php
    endif;
endif;
?>
