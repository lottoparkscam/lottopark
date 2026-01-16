<?php

use Fuel\Core\Input;
use Fuel\Core\View;
use Helpers\UrlHelper;
use Fuel\Core\Security;

$awaiting = get_query_var('action') === 'awaiting';

$user = Lotto_Settings::getInstance()->get('user');

/** @var Services_Currency_Calc $currency_calc */
$currency_calc = Container::get(Services_Currency_Calc::class);

# past tickets
/** @var Model_Whitelabel_User_Union_Ticket $past_ticket_dao */
$past_ticket_dao = Container::forge()->make(Model_Whitelabel_User_Union_Ticket::class);
$past_ticket_dao->only_past()->for_user($user['id']);
if (Input::get('status') && Input::get('status') !== 'a') {
    $past_ticket_dao->filter_status((int)Input::get('status'));
}
$past_ticket_count = $past_ticket_dao->get_count();

# pending (upcoming) tickets
/** @var Model_Whitelabel_User_Union_Ticket $pending_ticket_dao */
$pending_ticket_dao = Container::forge()->make(Model_Whitelabel_User_Union_Ticket::class);
$pending_ticket_dao->only_pending()->for_user($user['id']);
$pending_ticket_count = $pending_ticket_dao->get_count();

# results
/** @var Model_Whitelabel_User_Union_Ticket $tickets_dao */
$tickets_dao = Container::forge()->make(Model_Whitelabel_User_Union_Ticket::class);
$tickets_dao->for_user($user['id'])
->set_order_by(Input::get('sort', 'draw_date'), Input::get('sort_order', 'desc'));

$pagination_url = 'tickets/' . ($awaiting ? 'awaiting/' : '');
$pagination = new Helpers_View_Pagination(
    Helper_Route::get_by_slug('account', $pagination_url),
    ['id', 'created_at', 'ticket_amount', 'draw_date', 'prize', 'status'],
    Input::get(),
    !$awaiting ? $past_ticket_dao->get_count() : $pending_ticket_count
);

if (Input::get('status') && Input::get('status') !== 'a') {
    $tickets_dao->filter_status((int)Input::get('status'));
}
if ($awaiting) {
    $tickets_dao->only_pending();
} else {
    $tickets_dao->only_past();
}
$tickets = $tickets_dao->get_results($pagination->get_per_page(), $pagination->get_offset());

$get_image = function (Model_Whitelabel_User_Union_Ticket $ticket): string {
    if ($ticket->is_raffle) {
        return Lotto_View::get_lottery_image($ticket->raffle_id, null, 'raffle');
    }
    return Lotto_View::get_lottery_image($ticket->lottery_id);
};

$format_currency = function (float $amount, string $currency_code) use ($currency_calc, &$user): string {
    if (!$amount) {
        return '-';
    }

    $data = $currency_calc->convert_to_user_currency($amount, $currency_code, $user);

    return Lotto_View::format_currency(
        $data['amount'],
        $data['currency'],
        true
    );
};

$get_play_url = function (Model_Whitelabel_User_Union_Ticket $ticket): string {
    return $ticket->is_raffle ? sprintf('/play-raffle/%s', $ticket->lottery_slug) : '/play/' . $ticket->lottery_slug;
};

$humanize_status = function (int $status): string {
    switch ($status) {
        case Helpers_General::TICKET_STATUS_PENDING:
            return Security::htmlentities(_("pending"));
        case Helpers_General::TICKET_STATUS_WIN:
            return Security::htmlentities(_("win"));
        case Helpers_General::TICKET_STATUS_NO_WINNINGS:
            return Security::htmlentities(_("no winnings"));
        default:
            throw new InvalidArgumentException('Unsupported ticket payout status');
    }
};

$getPrize = function (Model_Whitelabel_User_Union_Ticket $ticket) use ($format_currency): string {
    $prize = $ticket->isRaffleWithoutInKindPrize() ? $format_currency((float)$ticket->prize, $ticket->currency_code) : _('Prize in kinds');
    return $ticket->isWin() ? $prize : '-';
}
?>
<?php if (!$awaiting): ?>
<?= View::forge(__DIR__ . '/components/ticket_status_filter.php', ['status' => (int)Input::get('status')]) ?>
<?php endif; ?>
<div class="myaccount-tickets-menu">
    <a href="<?=lotto_platform_get_permalink_by_slug('account').'tickets/awaiting'?>" class="myaccount-tickets-menu-item<?= $awaiting ? ' active' : ''?>">
        <?= Security::htmlentities(_("Upcoming Draws")); ?> <span>(<?= $pending_ticket_count ?>)</span>
    </a>

    <a href="<?= lotto_platform_get_permalink_by_slug('account') . 'tickets/' ?>" class="myaccount-tickets-menu-item<?= !$awaiting ? ' active' : ''?>">
        <?= Security::htmlentities(_("Past Tickets")); ?> <span>(<?= $past_ticket_count ?>)</span>
    </a>
</div>

<?php if (empty($tickets)): ?>
    <p><?= Security::htmlentities(_("No tickets.")); ?></p>
<?php else: ?>
    <table class="table table-transactions table-tickets table-sort">
        <thead>
        <tr>
            <th class="text-left tablesorter-header tablesorter-<?= htmlspecialchars($sort['id']['class']); ?>"
                data-href="<?= UrlHelper::esc_url($pagination->toggle_field_order('id')); ?>">
                <?= Security::htmlentities(_("Ticket ID and date")); ?>
            </th>
            <th class="text-left">
                <?= Security::htmlentities(_("Lottery name")); ?>
            </th>
            <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['ticket_amount']['class']); ?>"
                data-href="<?= UrlHelper::esc_url($pagination->toggle_field_order('ticket_amount')) ?>">
                <?= Security::htmlentities(_("Amount")); ?>
            </th>
            <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['draw_date']['class']); ?>"
                data-href="<?= UrlHelper::esc_url($pagination->toggle_field_order('draw_date')) ?>">
                <?= Security::htmlentities(_("Draw Date")); ?>
            </th>
            <th class="tablesorter-header tablesorter-0"
                data-href="<?= UrlHelper::esc_url($pagination->toggle_field_order('status')); ?>">
                <?= Security::htmlentities(_("Status")); ?>
            </th>
            <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['prize']['class']); ?>"
                data-href="<?= UrlHelper::esc_url($pagination->toggle_field_order('prize')); ?>">
                <?= Security::htmlentities(_("Prize")); ?>
            </th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
            <?php /** @var Model_Whitelabel_User_Union_Ticket $ticket */ ?>
        <?php foreach ($tickets as $ticket): ?>
            <tr>
                <td class="transactions-id">
                    <span class="tickets-id"><?= $whitelabel['prefix'] . $ticket->prefixed_token ?></span>
                    <br>
                    <span class="tickets-date">
                        <span class="fa fa-clock-o" aria-hidden="true"></span> <?= Helpers_View_Date::format_date_for_user_timezone($ticket->propertyRaw('created_at')) ?>
                    </span>
                </td>

                <td>
                    <span class="tickets-lottery">
                    <a href="<?= $get_play_url($ticket) ?>">
                        <img src="<?= $get_image($ticket) ?>" alt="<?= htmlspecialchars(_($ticket->id)) ?>">
                        <span class="tickets-lottery-name"><?= Security::htmlentities(_($ticket->lottery_name)); ?></span>
                    </a>
                    </span>
                </td>

                <td class="text-center">
                    <span class="mobile-only-label"><?= Security::htmlentities(_("Amount")); ?>:</span>
                    <?php if ((int)$ticket->transaction_payment_type === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) : ?>
                        <span class="transactions-amount"><?= Lotto_View::format_currency($ticket->transaction_bonus_amount, $ticket->transaction_currency_code, true) ?></span>
                        <span
                            class="info-circle fa fa-info-circle tooltip tooltip-bottom"
                            data-tooltip="<?= Security::htmlentities(_("Paid with bonus balance.")); ?>">
                        </span>
                    <?php else: ?>
                    <span class="transactions-amount"><?= Lotto_View::format_currency($ticket->ticket_amount, $ticket->currency_code, true) ?></span>
                    <?php endif; ?>
                </td>

                <td class="text-center transactions-date">
                    <span class="mobile-only-label"><?= Security::htmlentities(_("Draw Date")); ?>:</span>
                    <?php
                        $draw_date = $ticket->propertyRaw('draw_date');
                        if ($ticket->is_raffle) {
                            $timezone = $lotteries['__by_id'][$ticket->raffle_id]['timezone'];
                        } else {
                            $timezone = $lotteries['__by_id'][$ticket->lottery_id]['timezone'];
                        }
                        echo Helpers_View_Date::format_date_for_user_timezone($draw_date, $timezone);
                    ?>
                </td>

                <td class="transactions-status transactions-status-<?= $ticket->status ?>">
                    <?= $humanize_status($ticket->status) ?>
                </td>

                <td>
                    <?= $getPrize($ticket); ?>
                </td>

                <td class="text-center transactions-details text-nowrap">
                    <?php
                    $lottery = empty($ticket->lottery_id) ? null : $lotteries['__by_id'][$ticket->lottery_id];
                    $is_lottery_playable = empty($lottery) ? false : $lottery['is_enabled'] && !$lottery['is_temporarily_disabled'] && $lottery['playable'];
                    ?>
                    <?php if ((int)$ticket->status !== Helpers_General::TICKET_STATUS_PENDING && !$ticket->is_raffle && $is_lottery_playable): ?>
                        <a href="<?= sprintf('playagain/%s', $ticket->token) ?>"
                           class="tooltip tooltip-bottom"
                           data-tooltip="<?= Security::htmlentities(_("Play again")); ?>">
                            <span class="fa fa-refresh"></span>
                        </a>
                    <?php else:?>
                        <!-- <a href="javascript:void(0)" onclick="event.stopPropagation();" disabled="disabled" class="disabled"><span class="fa fa-refresh"></span></a> -->
                    <?php endif;
                    ?>
                    <a href="<?= Helper_Route::get_by_slug('account', 'tickets/details/' . $ticket->token); ?>" class="tooltip tooltip-bottom" data-tooltip="<?= Security::htmlentities(_("Details")); ?>">
                        <span class="fa fa-search"></span>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= View::forge(get_template_directory() . '/components/pagination.php', ['pagination' => $pagination]) ?>
<?php endif; ?>
