<?php
global $wp;

use Fuel\Core\Date;
use Orm\RecordNotFound;
use Models\WhitelabelRaffleTicket;
use Fuel\Core\Security;

$user = Lotto_Settings::getInstance()->get('user');
$whitelabel = !empty($whitelabel) ? $whitelabel : Lotto_Settings::getInstance()->get('whitelabel');

/** @var Services_Currency_Calc $currency_calc */
$currency_calc = Container::get(Services_Currency_Calc::class);

$token = (int)$wp->query_vars['id'];
/** @var WhitelabelRaffleTicket $tickets_dao */
$tickets_dao = Container::get(WhitelabelRaffleTicket::class);
try {
    /** @var WhitelabelRaffleTicket $ticket */
    $ticket = $tickets_dao->getByTokenAndUserId($token, $user['id'], ['lines', 'draw', 'raffle', 'transaction']);
} catch (Throwable $exception) {
    if ($exception instanceof RecordNotFound) {
        $ticket = null;
    } else {
        throw $exception;
    }
}

$format_date = function (string $timezone, ?Date $date = null): string {
    if (empty($timezone)) {
        $timezone = date_default_timezone_get();
    }
    if (empty($date)) {
        return '-';
    }
    return Lotto_View::format_date_without_timezone($date->format('mysql'), IntlDateFormatter::LONG, IntlDateFormatter::SHORT, null, $timezone, $timezone);
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

$format_number = function (int $number) use ($ticket) {
    return str_pad($number, strlen($ticket->raffle->getFirstRule()->max_lines_per_draw), "0", STR_PAD_LEFT);
};

$format_currency = function (float $amount, string $currency_code) use (&$user, $currency_calc): string {
    $data = $currency_calc->convert_to_user_currency($amount, $currency_code, $user);
    return Lotto_View::format_currency(
        $data['amount'],
        $data['currency'],
        true
    );
};

$draw_date = $ticket ? $format_date($user['timezone'], $ticket->draw_date) : null;

/** @coverage platform/fuel/app/tests/feature/wordpress/wp-content/Themes/Base/Box/RaffleMyAccountTicketsDetailsTest.php */
$getPreparedPrize = function (WhitelabelRaffleTicket $ticket) use ($format_currency): string {
    $prizesString = '';

    $hideInKindPrize = !($ticket->isWin() && $ticket->isRaffleWithInKindPrize());
    if ($hideInKindPrize) {
        return '';
    }

    /** eg. output for faireum-raffle 195 x GG World Million ticket, 5 x GG World X tickets, 10 x Mega Millions tickets */
    $prizes = array_column($ticket->lines, 'raffle_prize_id');
    $uniquePrizes = array_count_values($prizes);

    foreach ($uniquePrizes as $rafflePrizeId => $count) {
        $prizeName = '';
        foreach ($ticket->lines as $line) {
            if ($rafflePrizeId === (int) $line->raffle_prize_id) {
                $prizeName = $line->raffle_prize->tier->tier_prize->tier->tier_prize_in_kind->name;
                break;
            }
        }

        $prizeWithValidCount = str_replace('1 x', $count . ' x', $prizeName);
        $prizesString .= $prizeWithValidCount;

        $shouldAddComma = $rafflePrizeId !== array_key_last($uniquePrizes);
        if ($shouldAddComma) {
            $prizesString .= ', ';
        }
    }

    return $prizesString;
};

$currencies = Helpers_Currency::getCurrencies();

$getPrize = function (WhitelabelRaffleTicket $ticket) use ($format_currency, $currencies): string {
    $prize = $ticket->isRaffleWithoutInKindPrize() ? $format_currency((float)$ticket->prize, $currencies[$ticket['currency_id']]['code']) : _('Prize in kinds');
    return $ticket->isWin() ? $prize : '-';
}

?>
<?php if (empty($ticket)): ?>
    <div class="platform-alert platform-alert-error tickets-alert-error">
        <?= Security::htmlentities(_("Incorrect ticket.")); ?>
    </div>
<?php else: ?>
    <h1 class="account">
        <?= sprintf(_("Ticket %s%s"), $whitelabel['prefix'], $ticket->prefixed_token) ?>
    </h1>

    <div class="myaccount-data myaccount-details">

        <hr class="separator">
        <div class="myaccount-transactions">

            <div class="pull-left myaccount-details-image">
                <img src="<?= Lotto_View::get_lottery_image($ticket->raffle_id, null, 'raffle') ?>" alt="<?= $ticket->raffle->name ?>">
            </div>
            <div class="pull-left">
                <span class="myaccount-transactions-label"><?= Security::htmlentities(_("Lottery")) ?>:</span>
                <span class="myaccount-transactions-value tickets-lottery-name">
                    <a href="/play-raffle/<?= $ticket->raffle->slug ?>"><?= $ticket->raffle->name ?></a>
                </span>
                <br>
                <span class="myaccount-transactions-label"><?= Security::htmlentities(_("Status")) ?>:</span>
                <span class="myaccount-transactions-value">
                    <span class="transactions-status transactions-status-<?= $ticket->status ?>"><?= $humanize_status($ticket->status) ?></span>
                </span>
                <br>
                <span class="myaccount-transactions-label"><?= Security::htmlentities(_("Date")) ?>:</span>
                <span class="myaccount-transactions-value"><?= $format_date($user['timezone'], $ticket->created_at) ?></span>
                <br>
                <span class="myaccount-transactions-label"><?= Security::htmlentities(_("Draw date")) ?>:</span>
                <?php if (!empty($ticket->draw_date)): ?>
                <span class="myaccount-transactions-value myaccount-transaction-value-time">
                    <?= $format_date($ticket->raffle->timezone, $ticket->draw_date) ?> <span class="fa fa-clock-o tooltip" data-tooltip="<strong>Lottery Local Time:</strong> <?= $draw_date ?>"></span>
                    <span class="mobile-only-time"><?= $draw_date ?></span>
                </span>
                <?php else: ?>
                    <span class="myaccount-transactions-value myaccount-transaction-value-time">-</span>
                <?php endif; ?>
                <br>
                <span class="myaccount-transactions-label"><?= Security::htmlentities(_("Amount")); ?>:</span>
                <span class="myaccount-transactions-value">
                    <?php if ($ticket->transaction): ?>
                        <?php if ($ticket->transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE): ?>
                            <span class="transactions-amount">
                                <?= $format_currency($ticket->transaction->bonus_amount, $ticket->transaction->currency->code) ?>
                            </span>
                            <span
                                class="info-circle fa fa-info-circle tooltip tooltip-bottom"
                                data-tooltip="<?= Security::htmlentities(_("Paid with bonus balance.")); ?>">
                            </span>
                        <?php else: ?>
                            <span class="transactions-amount">
                                <?= $format_currency($ticket->transaction->amount, $ticket->transaction->currency->code) ?>
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="transactions-amount">
                            <?= $format_currency($ticket->amount, $ticket->currency->code) ?>
                        </span>
                    <?php endif; ?>
                </span>
                <br>
                <?php if ($ticket->isRaffleWithInKindPrize()):?>
                    <span class="myaccount-transactions-label"><?= Security::htmlentities(_("Prize in kinds")); ?>:</span>
                    <span class="myaccount-transactions-value">
                        <?= $getPreparedPrize($ticket) ?>
                    </span>
                    <br>
                <?php endif;?>

                <?php if (!$ticket->isRaffleWithInKindPrize() && $ticket->status === Helpers_General::TICKET_STATUS_WIN): ?>
                    <span class="myaccount-transactions-label"><?= _('Prize') ?>:</span>
                    <span class="myaccount-transactions-value">
                        <span class="transactions-amount"><?= $getPrize($ticket) ?></span>
                    </span>
                    <br>
                <?php endif;?>

                <?php if ($ticket->transaction): ?>
                    <span class="myaccount-transactions-label"><?= Security::htmlentities(_("Transaction ID")) ?>:</span>

                    <span class="myaccount-transactions-value">
                        <a href="<?= Helper_Route::get_by_slug('account', 'transactions/details/' . $ticket->transaction->token); ?>"><?= $whitelabel['prefix'] . 'P' . $ticket->transaction->token ?></a>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="clearfix"></div>
        <hr class="separator">
        <?php
        $drawNumbers = !empty($ticket->draw) && is_string($ticket->draw->numbers) ? json_decode($ticket->draw->numbers)[0] : [];
        $winningNumbers = !empty($drawNumbers) ? $drawNumbers : [];
        ?>
        
        <?php if (!$ticket->isRaffleWithInKindPrize() && ($ticket->status === Helpers_General::TICKET_STATUS_WIN || $ticket->status === Helpers_General::TICKET_STATUS_NO_WINNINGS)):?>
            <div class="draw-results">
                <h2><?= _('Raffle Results') ?></h2>
                <?php foreach ($winningNumbers as $number):?>
                    <div class="widget-chosen-ticket"><?php echo $format_number($number);?></div>
                <?php endforeach;?>
            </div>
        <?php endif;?>

        <?php if (!$ticket->isRaffleWithInKindPrize() && $ticket->status === Helpers_General::TICKET_STATUS_WIN): ?>
            <div class="draw-results">
                <h2><?= _('Winning numbers') ?></h2>
                <?php
                    foreach ($ticket->lines as $line) {
                        $isWinningNumber = in_array($line->number, $winningNumbers);
                        if ($isWinningNumber) {
                            echo '<div class="widget-chosen-ticket ">' . $format_number($line->number) . '</div>';
                        }
                    }
                ?>
                
            </div>

            <h3 class="ticket-prize"><?= _('Prize') ?>: <span><?= $getPrize($ticket) ?></span></h3>
        <?php endif;?>

        <hr>
        
        <div class="account-tickets">
            <?= Security::htmlentities(_("Ticket details")) ?>
        </div>

        <div class="widget-chosen-tickets-container widget-chosen-tickets-container-myaccount">
            <?php
                foreach ($ticket->lines as $line) {
                    $isWinningNumber = in_array($line->number, $winningNumbers);
                    echo '<div class="widget-chosen-ticket ticket-line-number-' . ($isWinningNumber ? 'win' : 'nowin'). '">' . $format_number($line->number) . '</div>';
                }
            ?>
        </div>
        <br>
        <div class="clearfix"></div>
    </div>
<?php endif; ?>
