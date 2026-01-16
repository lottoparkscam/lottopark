<?php

namespace Services;

use Carbon\Carbon;
use IntlDateFormatter;
use Lotto_View;
use Models\Raffle;

class RaffleResultService
{
    public function getWinnersTableHtml(array $prizes, Raffle $raffle): string
    {
        $winnersTableContentHtml = '';
        $showTicketsText = _('Show tickets');
        $counter = 0;

        foreach ($prizes as $prize) {
            $romanNumber = Lotto_View::romanic_number(++$counter);
            switch (true) {
                case $prize->tier->isPrizeInTickets():
                    $prizeValue = $prize->tier->tier_prize_in_kind->name;
                    break;
                case $prize->tier->isPrizeInKind():
                    $tierPrizeInKind = $prize->tier->tier_prize_in_kind;
                    $prizeAmountFormatted = Lotto_View::format_currency($prize->prize_amount, $prize->currency->code, true);
                    $prizeValue = $prizeAmountFormatted;
                    $prizeValue .= !empty($tierPrizeInKind) ? ' (' . $prize->tier->tier_prize_in_kind->name . ')' : '';
                    break;
                default:
                    $prizeValue = Lotto_View::format_currency($prize->prize_amount, $prize->currency->code, true);
            }

            $winnersTicket = '';
            $lines = $prize->lines;
            foreach ($lines as $line) {
                $numberFormatted = $this->formatLineNumber($line->number, $raffle);
                $winnersTicket .= <<<HTML
                    <div class="raffle-number" style="padding: 3px; margin-bottom: 3px;">$numberFormatted</div>
                HTML;
            }

            $winnersTableContentHtml .= <<<HTML
                <tr>
                    <td class="text-center">$romanNumber</td>
                    <td class="text-center">$prize->lines_won_count</td>
                    <td class="text-center">$prizeValue</td>
                    <td class="text-center">
                        <a data-tier="$prize->id" href="javascript:void(0)" 
                        class="raffle-show-results">$showTicketsText</a>
                    </td>
                </tr>
                <tr data-tier="$prize->id" class="hidden">
                    <td colspan="4">
                        <div class="widget-ticket-numbers">$winnersTicket</div>
                    </td>
                </tr>
            HTML;
        }

        return $winnersTableContentHtml;
    }

    public function getDateSelectOptions(array $raffleDraws, Raffle $raffle, int $drawId): string
    {
        $optionsHtml = '';

        foreach ($raffleDraws as $raffleDraw) {
            $dateFormatted = Lotto_View::format_date_without_timezone(
                $raffleDraw['date'],
                IntlDateFormatter::LONG,
                IntlDateFormatter::SHORT,
                null,
                $raffle->timezone,
                $raffle->timezone
            );

            $raffleDrawId = (int)$raffleDraw['id'];
            $isSelected = $drawId === $raffleDrawId ? 'selected="selected"' : '';

            $optionsHtml .= <<<HTML
                <option value="$raffleDrawId" $isSelected>
                    $dateFormatted
                </option>
             HTML;
        }

        return $optionsHtml;
    }

    public function formatLineNumber(int $number, Raffle $raffle): string
    {
        return str_pad(
            $number,
            strlen($raffle->getFirstRule()->max_lines_per_draw),
            '0',
            STR_PAD_LEFT,
        );
    }
}
