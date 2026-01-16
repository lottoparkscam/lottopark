<?php

namespace Services;

use Fuel\Core\Security;
use Helpers\UrlHelper;
use Helpers_Time;
use IntlDateFormatter;
use Lotto_Helper;
use Lotto_View;

class WidgetService
{
    public function getNextDrawFormattedForListWidget(array $lottery): string
    {
        $nextDrawTime = Lotto_View::next_real_draw_timestamp($lottery);
        $countdown = Lotto_View::next_draw_countdown($lottery);
        $minutesText = Lotto_View::shorten_time_translations(_("inline" . "\004" . "min"));

        $displayDaysFormat = $countdown->d >= 1;
        $displayMinutesFormat = $countdown->d < 1 && $countdown->h < 1;
        if ($displayDaysFormat) {
            $adjustedNextDrawHours = Lotto_Helper::adjust_time_to_display_hours($nextDrawTime);

            $adjustedNextDrawHumanDays = $countdown->d . ' ' . _("days");
            $hourInSeconds = Helpers_Time::HOUR_IN_SECONDS;
            $hoursText = floor(($nextDrawTime - $adjustedNextDrawHours) / $hourInSeconds) . ' ' . _("inline" . "\004" . "hrs");

            $adjustedNextDrawShortDays = Lotto_View::shorten_time_translations($adjustedNextDrawHumanDays);

            return $adjustedNextDrawShortDays . ' ' . $hoursText;
        } elseif ($displayMinutesFormat) {
            return <<<HTML
                <span class="countdown-item">
                    $countdown->i
                </span>
                $minutesText
            HTML;
        } else {
            $hours = $countdown->h;
            $minutes = $countdown->i;
            $hoursText = Lotto_View::shorten_time_translations(_("inline" . "\004" . "hrs"));

            return <<<HTML
                <span class="countdown-item">
                    $hours
                </span>
                $hoursText
                <span class="countdown-item">
                    $minutes
                </span>
                $minutesText
            HTML;
        }
    }

    public function getLastResultsHtml(
        array $lotteries,
        string $titleStartTag,
        string $titleEndTag,
        string $lotteryLink
    ): string
    {
        if (empty($lotteries)) {
            $noResultsText = _("No latest results.");
            return <<<HTML
                <div class="small-widget-no-info">
                    $noResultsText
                </div>
            HTML;
        }

        usort($lotteries, ["Helpers_Lottery", "sort_lotteries_by_last_date"]);

        $html = <<<HTML
         <div class="small-widget-content small-widget-results-items">
        HTML;

        $counter = 0;
        foreach ($lotteries as $lottery) {
            $lastDateLocal = $lottery['last_date_local'];
            if (is_null($lastDateLocal)) {
                continue;
            }

            $lastDateLocalFormatted = Lotto_View::format_date_without_timezone(
                $lastDateLocal,
                IntlDateFormatter::SHORT,
                IntlDateFormatter::NONE,
                'd MMMM',
                $lottery['timezone'],
                $lottery['timezone']
            );
            $lotteryName = Security::htmlentities(_($lottery['name']));

            $additionalData = unserialize($lottery['additional_data']);
            if ($additionalData === false) {
                $additionalData = null;
            }

            $preparedLotteryLink = UrlHelper::esc_url(rtrim($lotteryLink, '/') . '/' . $lottery['slug']);

            $lastNumbersFormatted = Lotto_View::format_line(
                $lottery['last_numbers'],
                $lottery['last_bnumbers'],
                null,
                null,
                null,
                $additionalData
            );

            $html .= <<<HTML
                <div class="small-widget-results-item">
                    <div class="pull-left">
                        $titleStartTag
                            <a href="$preparedLotteryLink">
                                $lotteryName
                            </a>
                        $titleEndTag
                    </div>
                    <div class="pull-right small-widget-results-date">
                        <span class="fa fa-clock-o" aria-hidden="true"></span>
                        $lastDateLocalFormatted
                    </div>
                    <div class="clearfix"></div>
                    $lastNumbersFormatted
                </div>
            HTML;

            if ($counter === 2) {
                break;
            }
            $counter++;
        }
        $html .= "</div>";

        return $html;
    }
}
