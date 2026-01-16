<?php

use Carbon\Carbon;
use Helpers\AssetHelper;
use Helpers\UrlHelper;
use Models\WhitelabelUserTicket;
use Repositories\Aff\WhitelabelAffRepository;

if (!defined('WPINC')) {
    die;
}

$isTransactionPurchase = (int)$transaction->type === Helpers_General::TYPE_TRANSACTION_PURCHASE;
$isTransactionDeposit = (int)$transaction->type === Helpers_General::TYPE_TRANSACTION_DEPOSIT;
$whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
$parentAffiliateToken = $whitelabelAffRepository->getAffiliateParentTokenByWhitelabelUserId($transaction->whitelabel_user_id);

$isKenoTicketPurchased = false;

$user_timezone = get_user_timezone();
$corder_inner_obj = new Forms_Wordpress_Myaccount_Corder();
$additional_success_text = $corder_inner_obj->get_additional_success_text($transaction);
?>

<?php if (!empty($additional_success_text)):?>
    <article class="page">
        <p class="text-center additional-payment-text"><?= $additional_success_text; ?></p>
    </article>
<?php endif;?>

<?php include('payment/additional_text.php');?>

<?php if ((isset($tickets) && count($tickets)) > 0 || $isTransactionDeposit):?>

    <div class="bs-container-fluid">

        <?php if ($is_free_ticket == true):?>
            <div class="bs-row">
                <div class="bs-col-12 free-ticket-content <?= $isTransactionPurchase ? 'center-content' : ''; ?>">
                    <?php $link = sprintf(_('<a href="%s">%s</a>'), $my_tickets_link, _('my tickets'));?>
                    <span><?= sprintf(_("Free ticket (see in %s)"), $link); ?></span>
                </div>
            </div>
        <?php endif;?>

        <?php
        if ($isTransactionPurchase):
            $saved_multidraws = [];
            foreach ($tickets as $ticket):
                $isMultiDrawTicket = !empty($ticket['multi_draw_id']) && in_array($ticket['multi_draw_id'], $saved_multidraws);
                if ($isMultiDrawTicket) {
                    continue;
                }

                $isMultiDraw = false;
                if (!empty($ticket['multi_draw_id'])) {
                    array_push($saved_multidraws, $ticket['multi_draw_id']);
                    $isMultiDraw = true;
                }

                $lottery = $lotteries['__by_id'][$ticket['lottery_id']];
                $pricing = Lotto_Helper::get_user_converted_price($lottery, $ticket['currency_id']);
                $isTicketKeno = Helpers_Lottery::is_keno($lottery);

                list(
                    $towin,
                    $formatted_thousands
                ) = Lotto_View::get_jackpot_formatted_to_text(
                    $lottery['current_jackpot'],
                    $lottery['currency'],
                    Helpers_General::SOURCE_WORDPRESS,
                    $lottery['force_currency']
                );

                $lottery_image = Lotto_View::get_lottery_image($lottery['id']);
                $play_info_href = lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']);
                $play_text = sprintf(_('<a href="%s">%s</a> ticket'), $play_info_href, _($lottery['name']));
                $allowed_html_play = array("a" => array("href" => array()));
                $mobile_hide_text = sprintf(_("%s to win"), $towin);
                $allowed_html_mobile = [
                    "span" => [
                        "class" => [],
                        "aria-hidden" => [],
                        "data-tooltip" => [],
                    ]
                ];
                $allowed_html_order = [
                    "span" => [],
                    "strong" => [],
                    "br" => []
                ];

                $full_next_date_local = $ticket['draw_date'];
                $drawDateValue = Helpers_View_Date::format_date_for_user_timezone($full_next_date_local, $lottery['timezone']);

                $ticketMultiplier = null;
                if (isset($ticket['ticket_multiplier'])) {
                    $ticketMultiplier = $ticket['ticket_multiplier'];
                }
                $numbersPerLine = null;
                ?>

                <?php if ($isTicketKeno):
                    $numbersPerLine = $ticket['numbers_per_line'];
                    $nextDraw = Lotto_Helper::get_lottery_real_next_draw($lottery);
                    $nextDraw->setTimezone($user_timezone ?? 'UTC');
                    $next_draw_timestamp = $nextDraw->getTimestamp();
                    $nextDraw = Carbon::createFromTimestamp($next_draw_timestamp);
                    $now = Carbon::now($user_timezone);
                    $drawDateDiff = $now->diff($nextDraw);

                    if (!$isKenoTicketPurchased):
                        $kenoNumberOfballsPerDraw = !empty($lottery['last_numbers']) ? count(explode(',', $lottery['last_numbers'])) : 20;
                        $isKenoTicketPurchased = true;
                        ?>
                        <script>
                            window.lotterySlug = '<?= $lottery['slug'] ?>';
                        </script>

                        <div class="bs-row <?php echo (!Helpers_Lottery::isQuickKeno($lottery)) ? 'd-none': ''?>">
                            <div class="bs-col-12 col-animation">
                                <?php get_template_part('components/keno-animation', null, [
                                    'is-enabled' => Helpers_Lottery::isQuickKeno($lottery),
                                    'img-ball' => $lottery_image,
                                    'numbers-drawn-count' => $kenoNumberOfballsPerDraw,
                                    'link-page-play' => $play_info_href,
                                    'next-draw-timestamp' => $next_draw_timestamp,
                                    'timer-data' => $drawDateDiff,
                                ]);?>
                            </div>
                        </div>

                        <div class="bs-row">

                            <article class="bs-col-12 keno-article">
                                <h1 class="header-result">Keno <?= ucwords(_('Draw result')) ?></h1>
                                <p class="text-result"><?= _('The draw will take place soon! ') . '<b>' . _('Please wait on this page, do not refresh or close it. ') . '</b><br/>' . _('The draw results will be displayed automatically.')?></p>
                            </article>

                            

                            <div class="bs-col-12 content-center">
                                <div id="balls" class="ticket-line balls-draw">
                                    <?php for ($i = 0; $i < $kenoNumberOfballsPerDraw; $i++):?>
                                        <div class="ticket-line-number thank-you-page-results"> ? </div>
                                    <?php endfor;?>
                                </div>
                            </div>
                            
                        </div>
                    <?php endif;?>
                <?php endif;?>

                <div class="bs-row">
                    <div class="bs-col-12 col-play-again">
                        <div id="play-again" class="play-again-section">
                            <div id="play-again-paragraph-results">
                                <div class="keno platform-alert platform-alert-success hide"><span class="fa fa-check-circle keno"></span><span id="text-after-results"></span></div>
                            </div>
                            <div id="button-play-again" class="play-again"></div>
                        </div>
                    </div>
                </div>

                <?php
                if ($isMultiDraw) {
                    $multiDrawHelper = new Helpers_Multidraw([]);
                    $drawDateText = sprintf(_("First draw event on %s"), $drawDateValue);

                    $amount = $ticket['multi_draw_amount'];
                    if ((int)$transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) {
                        $amount = $ticket['multi_draw_bonus_amount'];
                    }

                    $amount_value = Lotto_View::format_currency($amount, $currencies[$ticket['currency_id']]['code'], true);
                    $order_value = Lotto_View::format_currency(round($ticket['multi_draw_old_ticket_price'] / $ticket['count'], 2), $currencies[$ticket['currency_id']]['code'], true);

                    $order_text = sprintf(
                        _("Lines: <span>%s</span> &times; %s &times; %s draws"),
                        $ticket['count'],
                        $order_value,
                        $ticket['multi_draw_tickets']
                    );
                    $old_price = $multiDrawHelper->calculate_old_price($ticket['multi_draw_tickets'], $pricing, $ticket['count']);
                    $old_price_with_currency = Lotto_View::format_currency($old_price, $currencies[$ticket['currency_id']]['code'], true);
                    $order_text .= " (" . $old_price_with_currency . ")";

                    $order_text .= "<br/>" . sprintf(
                        _("<span>%s</span> multi-draw discount"),
                        $ticket['multi_draw_discount'] . '%'
                    );

                    $discount_with_currency = Lotto_View::format_currency($amount, $currencies[$ticket['currency_id']]['code'], true);
                    $order_text .= " (" . $discount_with_currency . ")";
                } else {
                    $drawDateText = sprintf(_("Draw event on %s"), $drawDateValue);

                    $amount = $ticket['amount'];
                    $line_price = $ticket['line_price'];
                    if ((int)$transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) {
                        $amount = $ticket['bonus_amount'];
                        $line_price = $ticket['bonus_line_price'];
                    }
                    $amount_value = Lotto_View::format_currency($amount, $currencies[$ticket['currency_id']]['code'], true);

                    if (isset($ticket['ticket_multiplier'])) {
                        $order_value = Lotto_View::format_currency(bcdiv($ticket['line_price'], $ticket['ticket_multiplier'], 2), $currencies[$ticket['currency_id']]['code'], true);
                        $order_text = sprintf(
                            _("Lines: <span>%s</span> &times; %s &times; %s (Multiplier)"),
                            $ticket['count'],
                            $order_value, //because at this point ticket's line price is multiplied
                            $ticket['ticket_multiplier']
                        );
                    } else {
                        $order_value = Lotto_View::format_currency($line_price, $currencies[$ticket['currency_id']]['code'], true);
                        $order_text = sprintf(
                            _("Lines: <span>%s</span> &times; %s"),
                            $ticket['count'],
                            $order_value
                        );
                    }
                }
                ?>
                <div class="bs-row">
                    <div class="bs-col-lg-6 bs-offset-lg-3 ticket-success">
                        <div class="order-summary-image">
                            <img src="<?= UrlHelper::esc_url($lottery_image); ?>" alt="<?= Security::htmlentities(_($lottery['name'])); ?>">
                        </div>
                    
                        <div class="order-summary-content">
                            <span class="order-summary-content-header">
                                <?= wp_kses($play_text, $allowed_html_play); ?>
                                <span class="mobile-hide"> - </span>
                                <?= wp_kses($mobile_hide_text, $allowed_html_mobile); ?>
                                <br class="mobile-hide">
                            </span>
                            <span class="order-summary-content-desc"><?= wp_kses($order_text, $allowed_html_order); ?></span>
                            <?php if (!is_null($numbersPerLine)): ?>
                                <span class="order-summary-content-npl"><?php echo sprintf(_("Playing %s numbers per line."), $numbersPerLine); ?></span>
                            <?php endif; ?>
                            <span class="order-summary-content-date"><?= $drawDateText; ?></span>
                        </div>
                        <div class="col-amount"><?= Security::htmlentities($amount_value); ?></div>
                    </div>
                </div>

                <div class="bs-row">
                    <div class="bs-col-12">

                        <div class="ticket-details-info">
                            <article class="keno-article">
                                <h1 class="header-result"><?= ucwords(_('Ticket details')) ?></h1>
                            </article>
                        </div>

                        <?php
                        $rowBalls = '<div class="balls-container">';
                        $ticketId = $ticket['id'];
                        /** @var $ticket WhitelabelUserTicket */
                        $ticket = WhitelabelUserTicket::find($ticketId);
                        $lines = $ticket->whitelabelUserTicketLines;


                        if ($isTicketKeno) {
                            $kenoTicketToken = $ticket->token;
                        }

                        foreach ($lines as $line) {
                            $rowBalls .= '<div class="row-numbers">';
                            $numbers = $line->numbers;
                            $numbers = explode(',', $numbers);

                            $bonusNumbers = $line->bnumbers;
                            $bonusNumbers = explode(',', $bonusNumbers);

                            foreach ($numbers as $number) {
                                $ticketClass = $isTicketKeno ? "ticket-line-number keno-balls-number-thank-you-page thank-you-page-ball" : "ticket-line-number thank-you-page-ball";
                                $rowBalls .= '<div class="' . $ticketClass . ' ">' . $number . '</div>';
                            }
                            foreach ($bonusNumbers as $bonusNumber) {
                                if (!empty($bonusNumber)) {
                                    $rowBalls .= '<div class="ticket-line-bnumber thank-you-page-bonus-ball">' . $bonusNumber . '</div>';
                                }
                            }
                            $rowBalls .= '</div>';
                        }

                        echo $rowBalls . '</div>';
                        ?>
                    </div>
                </div>

            <?php
            endforeach;

        else:
            $amount_value = Lotto_View::format_currency(
                $transaction->amount,
                $currencies[$transaction['currency_id']]['code'],
                true
            );
            ?>
            <div class="bs-row">
                <div class="bs-col-12">
                    <p class="payment-deposit-content"><?= Security::htmlentities(_("Deposit")); ?></p>
                    <p class="text-right col-amount"><?= Security::htmlentities($amount_value); ?></p>
                </div>
            </div>
        <?php endif;?>
        
    </div>

    <?php if ($isKenoTicketPurchased):?>
        <script>
            window.ticketToken = "<?= $kenoTicketToken ?>";
            window.ticketStatusWin = <?= Helpers_General::TICKET_STATUS_WIN ?>;
            window.resultHeader = "<?= _('Pending') . ' ' . _('Results') ?>";
            window.drawHeader = "<?= _('Results') ?>";
            window.buttonLabel = "<?= _('Play again') ?>";
            window.kenoUrl= "<?= lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']); ?>";
            window.messageLose ="<?= _('No luck this time? Try again!') ?> ";
            window.messageWin ="<?= _('Congratulations! Play again to win even more!') ?> ";
        </script>
        <?php wp_enqueue_script('page-success', AssetHelper::mix('js/PageSuccess.min.js', AssetHelper::TYPE_WORDPRESS, true), ['jquery'], false, true);?>
    <?php endif;?>

    <?php if ($isTransactionPurchase):?>
        <script>
            window.transactionType = 'purchase';
            window.parentAffiliateToken = '<?= $parentAffiliateToken ?>';
            window.orderTitle = '<?php echo $lottery['name'];?>';
            window.orderId = '<?php echo $transaction['token'];?>';
            window.orderAmount = <?php echo $transaction['amount_usd'];?>;
            window.purchaseData = <?php echo json_encode($purchaseData); ?>;
        </script>
    <?php endif;?>

    <?php if ($isTransactionDeposit):?>
        <script>
            window.transactionType = 'deposit';
            window.parentAffiliateToken = '<?= $parentAffiliateToken ?>';
            window.orderId = '<?php echo $transaction['token'];?>';
            window.orderAmount = <?php echo $transaction['amount_usd'];?>;
            window.paymentMethodId = <?php echo $transaction['whitelabel_payment_method_id'];?>;
            window.depositData = <?php echo json_encode($depositData); ?>;
        </script>
    <?php endif;?>

<?php endif;?>
