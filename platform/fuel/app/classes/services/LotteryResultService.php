<?php

namespace Services;

use Container;
use Helpers_Lottery;
use Lotto_View;

class LotteryResultService
{
    private LotteryAdditionalDataService $lineServices;

    public function __construct()
    {
        $this->lineServices = Container::get(LotteryAdditionalDataService::class);
    }

    public function getLotteryResultTableHtml(array $lottery, array $drawData, array $lotteryType, array $lotteryDraw): string
    {
        $lotteryNotExistsOrNoDrawData = empty($lottery) || empty($drawData);
        if ($lotteryNotExistsOrNoDrawData) {
            return '';
        }

        $lotteryCurrency = $lottery['currency']['code'];
        $tierText = _('Tier');
        $matchText = sprintf(_("Match %s"), '<div class="ticket-line-number">' . _('X') . '</div>');

        $isExtraNumber = $lotteryType['bextra'] > 0;
        if ($lotteryType['bcount'] > 0 || $isExtraNumber) {
            $matchText .= '+';
            $matchText .= sprintf("%s", '<div class="ticket-line-bnumber">' . _('X') . '</div>');
        }

        if ($lotteryType['additional_data']) {
            $lotteryTypeAdditionalData = unserialize($lotteryType['additional_data']);
            $ballShortname = $this->lineServices->getBallShortName($lotteryTypeAdditionalData);

            if ($isExtraNumber) {
                $matchText .= '+';
                $matchText .= sprintf("%s", '<div class="ticket-line-bnumber">' . _($ballShortname) . '</div>');
            }
        }

        $winnersText = _('Winners');
        $payoutPerWinnerText = _('Payout per winner');

        $html = <<<HTML
            <table class="table table-results-detailed" data-currency="$lotteryCurrency">
                <thead>
                    <tr>
                        <th class="text-left">$tierText</th>
                        <th class="text-left">$matchText</th>
                        <th>$winnersText</th>
                        <th class="text-right">$payoutPerWinnerText</th>
                    </tr>
               </thead>
               <tbody>
        HTML;

        $isSupportTicketMultipliers = Helpers_Lottery::supports_ticket_multipliers_by_lottery_id($lottery['id']);
        foreach ($drawData as $key => $draw) {
            $multiplierAttribute = $isSupportTicketMultipliers ? 'data-multiplier="' . $draw['multiplier'] . '"' : '';
            $prizeText = sprintf(_("Prize #%d"), $key + 1);
            $romanNumber = Lotto_View::romanic_number($key + 1);
            $matchText = sprintf(_("Match %s"), '<div class="ticket-line-number ticket-line-number-small">' . _('X') . '</div>');
            $isBNumber = $lotteryType['bextra'] == 0 || ($isExtraNumber && $draw['match_b']);
            $bNumberHtml = '';

            if ($isBNumber) {
                $bNumberHtml .= '+&nbsp;';
                $bNumberHtml .= sprintf("%s", '<div class="ticket-line-bnumber ticket-line-number-small">' . _('X') . '</div>');
            }

            $isRefund = isset($lotteryTypeAdditionalData['refund']) && $lotteryTypeAdditionalData['refund'] == 1;
            $refundText = ($draw['match_n'] == 0 && $isRefund) ? _('R') : $draw['match_n'];
            $extraRefundText = '';

            if ($lotteryType['additional_data']) {
                $itemAdditional = unserialize($draw['additional_data']);
                if (isset($itemAdditional['refund']) && $itemAdditional['refund'] == 1 && $draw['match_n'] > 0) {
                    $extraRefundText .= '+&nbsp;';
                    $extraRefundText .= _('R');
                } elseif (isset($itemAdditional['super']) && $itemAdditional['super'] == 1 && $draw['match_n'] > 0)
                {
                    $extraRefundText .= '+&nbsp;';
                    $extraRefundText .= _('S');
                }
            }

            $lotteryExtraText = '';

            if ($lotteryType['bcount'] > 0 || $isExtraNumber) {
                if ($lotteryType['bextra'] == 0 || ($isExtraNumber && $draw['match_b'])) {
                    $lotteryExtraText .= '+&nbsp;';
                    $lotteryExtraText .= $draw['match_b'];
                }
            }

            $winnersText = _('Winners');
            $formattedWinners = Lotto_View::format_number($draw['winners']);

            $quickPickText = $draw['type'] == 2 ? _('Free Quick Pick') : Lotto_View::format_currency(
                $draw['prizes'],
                $lottery['currency']['code'],
                true
            );

            $html .= <<<HTML
                <tr $multiplierAttribute>
                     <td class="text-left">
                        <span class="mobile-only-label">$prizeText</span>
                        <span class="mobile-hide">$romanNumber</span>
                    </td>
                    <td class="text-left">
                        <span class="mobile-only-label">
                            $matchText
                            $bNumberHtml
                        </span>
                        $refundText
                        $extraRefundText
                        $lotteryExtraText
                    </td>
                    <td class="text-center">
                        <span class="mobile-unbold mobile-only-label">$winnersText:</span>
                        &nbsp;
                        <span class="table-results-detailed-winners">$formattedWinners</span>
                    </td>
                    <td class="text-right table-results-detailed-jackpot">
                        <span class="mobile-unbold mobile-only-label">$payoutPerWinnerText:</span>
                        &nbsp;
                        <span class="table-results-detailed-amount">$quickPickText</span>
                    </td>
                </tr>
            HTML;
        }

        $totalSumText = _('Total Sum');
        $totalWinnersText = _('Total Winners');
        $totalPrizesText = _('Total Prize');
        $totalWinnersFormatted = Lotto_View::format_number($lotteryDraw['total_winners']);
        $totalPrizeFormatted = Lotto_View::format_currency($lotteryDraw['total_prize'], $lotteryCurrency, true);

        $html .= <<<HTML
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-left">$totalSumText</td>
                    <td class="text-center">
                        <span class="mobile-only-label">$totalWinnersText</span>
                        &nbsp;
                        <span class="table-results-detailed-winners">$totalWinnersFormatted</span>
                    </td>
                    <td class="text-right table-results-detailed-jackpot">
                        <span class="mobile-only-label">$totalPrizesText</span>
                        &nbsp;
                        <span class="table-results-detailed-amount">$totalPrizeFormatted</span>
                    </td>
                </tr>
            </tfoot>
        </table>
       HTML;

        return $html;
    }
}
