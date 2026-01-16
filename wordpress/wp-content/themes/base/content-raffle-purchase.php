<?php

use Models\Currency;
use Models\RaffleDraw;
use Models\RaffleRuleTier;
use Modules\Account\Reward\PrizeType;

if (!defined('WPINC')) {
    die;
}

global $error, $ticket;

$raffle = $ticket->raffle;

if (!$raffle->is_turned_on) {
    wp_redirect(lotto_platform_home_url('/'), '301');
    exit();
}

if (empty($error)) {
    $raffle_image = Lotto_View::get_lottery_image($ticket->raffle_id, null, 'raffle');
}

$user = Lotto_Settings::getInstance()->get('user');
$whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
$container = Container::forge();

            /** @var RaffleDraw $draw_dao */
            $draw_dao = $container->get(RaffleDraw::class);
$draw_no = $draw_dao->getCount() + 1;

            /** @var Currency $currency_dao */
            $currency_dao = $container->get(Currency::class);
$user_currency = $currency_dao->get_by_id($user['currency_id'])->code;

/** @var Services_Currency_Calc $currency_calc */
$currency_calc = $container->get(Services_Currency_Calc::class);

$calc_currency_to_user = function (float $amount, string $currency_code) use (&$user, $currency_calc, &$user_currency) {
    return $currency_calc->convert_to_any($amount, $currency_code, $user_currency);
};

$user_line_price = $calc_currency_to_user($ticket->raffle->getFirstRule()->line_price, $ticket->raffle->getFirstRule()->currency->code);
$user_total_price = $calc_currency_to_user($ticket->amount, $ticket->raffle->getFirstRule()->currency->code);

// ---------------------- UTILS ----------------------
$format_number = function (int $number) use ($ticket) {
    return str_pad($number, strlen($ticket->raffle->getFirstRule()->max_lines_per_draw), "0", STR_PAD_LEFT);
};
$is_paid_with_bonus_balance = function () use ($ticket) {
    return ($ticket->transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE);
};

$is_prize_in_tickets = function (RaffleRuleTier $tier) {
    $tier_prize_in_kind = $tier->tier_prize_in_kind;
    return !empty($tier_prize_in_kind) && $tier_prize_in_kind->type === PrizeType::TICKET;
};

$purchaseData = [
    'event' => 'purchase',
    'user_id' => $user ? $whitelabel['prefix'] . 'U' . $user['token'] : '',
    'transaction_id' => $whitelabel['prefix'] . $ticket->transaction->token,
    'value' => $ticket->transaction->amount,
    'currency' => lotto_platform_user_currency(),
    'items' => [
      [
        'item_id' => $ticket->raffle->slug,
        'item_name' => $ticket->raffle->name,
        'lines' => count($ticket->lines),
        'price' => $ticket->cost,
        'item_variant' => 'single',
      ]
    ]
];
?>
<div class="content-area<?php
echo Lotto_Helper::get_widget_main_area_classes("play-lottery-sidebar-id", "play-lottery-more-sidebar-id");
?>">

    <div class="main-width content-width">
        <div class="content-box">
            <?php if (empty($error)): ?>
                <article class="page">
                    <h1 class="text-center"><?= _('Thank you and good luck!') ?></h1>
                    <p class="text-center"><?= _('Thank you! Your payment has been successfully processed.') ?></p>
                </article>
                <section class="page">
                    <div class="ticket-card" style="padding: 15px;">
                        <div class="ticket-card-logo">
                            <img src="<?= $raffle_image ?>" alt="Raffle Logo"/>
                        </div>
                        <div class="ticket-card-details">
                            <div class="ticket-card-raffle-name">
                                <a href="<?php echo lotto_platform_get_permalink_by_slug('play-raffle/' . $ticket->lottery['slug']);?>"><strong><?= $ticket->raffle->name ?></strong></a> ticket 
                            </div>
                            <div>
                                <?=
                                    $is_prize_in_tickets(reset($ticket->raffle->getFirstRule()->tiers)) ?
                                        reset($ticket->raffle->getFirstRule()->tiers)->tier_prize_in_kind->name:
                                        Lotto_View::format_currency($ticket->raffle->main_prize, $ticket->raffle->currency->code, true);
                                ?> to win
                            </div>
                            <div><?= _('Draw number') ?>: <b><?= $draw_no ?></b></div>
                            <div>
                                Lines: <?= count($ticket->lines) ?> x <?= Lotto_View::format_currency($user_line_price, $user_currency, true); ?>
                            </div>
                            <div>
                                <?= _('Selected numbers') ?> (<?= count($ticket->lines) ?>):
                            </div>
                            <div class="widget-ticket-numbers">
                                <?php foreach ($ticket->lines as $line): ?>
                                    <span class="raffle-number"><?= $format_number($line->number) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="ticket-card-amount">
                            <?= $is_paid_with_bonus_balance() ?
                                Lotto_View::format_currency($ticket->transaction->bonus_amount, $ticket->transaction->currency->code, true) :
                                Lotto_View::format_currency($ticket->transaction->amount, $ticket->transaction->currency->code, true);
                            ?>
                        </div>
                    </div>
                </section>
            <?php else: ?>
                <article class="page">
                    <div class="platform-alert platform-alert-error">
                        <h3 class="text-center">An error occurs</h3>
                        <p class="text-center"><?= $error ?></p>
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    window.transactionType = 'purchase';
    window.orderTitle = '<?php echo $raffle['name'];?>';
    window.orderId = '<?php echo $ticket->transaction->token;?>';
    window.orderAmount = <?php echo ($ticket->transaction->amount_usd !== 0) ? $ticket->transaction->amount_usd : $ticket->transaction->bonus_amount_usd;?>;
    window.purchaseData = <?php echo json_encode($purchaseData); ?>;
</script>
