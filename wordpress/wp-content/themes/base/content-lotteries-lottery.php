<?php

use Carbon\Carbon;
use Models\Lottery;

if (!defined('WPINC')) {
    die;
}

$lottery = lotto_platform_get_lottery_by_slug($post->post_name);
$is_keno = false;
if ($lottery !== false) {
    $is_keno = Helpers_Lottery::is_keno($lottery);
}
$is_not_keno = !$is_keno;

list(
    $social_share_rows,
    $counter_socials,
    $current_url
    ) = Helpers_General::get_prepared_social_share_links();
?>
<div class="content-area<?php
echo Lotto_Helper::get_widget_main_area_classes(null, "lottery-info-more-sidebar-id");
?>">
    <?php
    // These are settings important for showing lottery submenu properly
    $category = null;
    $show_main_width_div = true;
    $relative_class_value = '';
    $selected_class_values = [
        0 => false,             // Play submenu
        1 => false,             // Results submenu
        2 => true,              // Information submenu
        3 => false,             // News submenu
    ];
    include('box/lottery/submenu.php');

    get_active_sidebar('lottery-info-sidebar-id');
    ?>
    <div class="main-width content-width">
        <div class="content-box<?php
        echo Lotto_Helper::get_widget_top_area_classes("lottery-info-sidebar-id");
        echo Lotto_Helper::get_widget_bottom_area_classes("lottery-info-more-sidebar-id");
        ?>">
            <section class="page-content">
                <article class="page">
                    <h1><?php the_title(); ?></h1>
                </article>
            </section>
            <?php if (is_active_sidebar('lottery-info-content-sidebar-id')): ?>
            <div class="content-box-main">
                <?php endif; ?>
                <section class="page-content">
                    <article class="page">
                        <?php $page_name = "lotteries";
                        include('box/lottery/group.php'); ?>
                        <?php
                        $post_data = get_extended($post->post_content);
                        echo apply_filters('the_content', $post_data['main']);
                        ?>
                    </article>
                </section>
                <?php
                if (!empty($lottery) && is_array($lottery)):
                    /* moved */
                    $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
                    $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
                    $lottery = null;
                    $lottery_type = null;

                    $lottery_additional_data = null;
                    $lottery_type_additional_data = null;

                    if (isset($post) && isset($lotteries['__by_slug'][$post->post_name])) {
                        $lottery = $lotteries['__by_slug'][$post->post_name];
                        $is_keno = Helpers_Lottery::is_keno($lottery);
                        $lottery_type = Model_Lottery_Type::get_lottery_type_for_date($lottery, Lotto_Helper::get_lottery_real_next_draw($lottery, Lotto_Helper::is_lottery_closed($lottery, null, $whitelabel) ? 2 : 1)->format('Y-m-d'));
                        $lottery_types = Model_Lottery_Type_Data::get_lottery_type_data($lottery);

                        if ($lottery['additional_data']) {
                            $lottery_additional_data = unserialize($lottery['additional_data']);
                            if ($lottery_additional_data === false) {
                                $lottery_additional_data = null;
                            }
                        }

                        if ($lottery_type['additional_data']) {
                            $lottery_type_additional_data = unserialize($lottery_type['additional_data']);
                        }
                    }

                    $is_not_keno = !$is_keno;
                    $full_last_date_local = $lottery['last_date_local'];

                    $last_date_local = Lotto_View::format_date(
                        $full_last_date_local,
                        IntlDateFormatter::MEDIUM,
                        IntlDateFormatter::LONG,
                        $lottery['timezone'],
                        false,
                    );

                    $last_date_local_text = Security::htmlentities($last_date_local);

                    $line_formatted = Lotto_View::format_line(
                        $lottery['last_numbers'],
                        $lottery['last_bnumbers'],
                        null,
                        null,
                        null,
                        $lottery_additional_data
                    );
                    $line_options = [
                        "div" => [
                            "class" => [],
                            "data-tooltip" => []
                        ],
                        "span" => []
                    ];
                    $line_formatted_text = wp_kses($line_formatted, $line_options);

                    $schedule_date = Lotto_View::format_draw_dates(
                        $lottery['draw_dates'],
                        $lottery['timezone']
                    );
                    $schedule_options = [
                        "span" => [
                            "class" => [],
                            "data-tooltip" => []
                        ],
                        'br' => [],
                        'strong' => []
                    ];
                    $schedule_date_text = wp_kses($schedule_date, $schedule_options);

                    if ($is_keno) {
                        $lottery_numbers_per_line = Lotto_Helper::get_numbers_per_line_array($lottery['id']);
                        $guess_range = min($lottery_numbers_per_line) . ' &ndash; ' . max($lottery_numbers_per_line) . ' / ' . $lottery_type['nrange'];
                    } else {
                        $guess_range = $lottery_type['ncount'] . '/' . $lottery_type['nrange'];
                        if (isset($lottery_type['bcount']) &&
                            (int)$lottery_type['bcount'] > 0 &&
                            (int)$lottery_type['bextra'] === 0
                        ) {
                            $guess_range .= ' + ' . $lottery_type['bcount'] . '/' . $lottery_type['brange'];
                        }
                    }


                    $guess_range_text = Security::htmlentities($guess_range);
                    ?>
                    <?php if (!is_null($lottery['last_numbers'])): ?>
                    <div class="info-mobile-show">
                        <div class="info-mobile-latest-results">
                            <h2 class="info-mobile-latest-results-header"><?= _('Latest Result') ?>
                                <span data-type="content-lotteries-last-draw-text"><?php if (!is_null($lottery['last_date_local'])): ?>
                                        (<?= $last_date_local_text; ?>)<?php endif; ?></span></h2>
                            <span class="mobile-only-label"><?= _('Latest Result') ?>
                                    (<?= $last_date_local_text; ?>)</span>
                            <span data-type="content-lotteries-last-result-numbers">
                                <?= $line_formatted_text; ?>
                            </span>
                        </div>
                        <?php 
                        if (is_active_sidebar('lottery-info-content-sidebar-id')):
                            Lotto_Helper::widget_before_area('lottery-info-content-sidebar-id');
                            dynamic_sidebar('lottery-info-content-sidebar-id');
                            Lotto_Helper::widget_after_area('lottery-info-content-sidebar-id');
                        endif;
                        ?>
                    </div>
                <?php endif; ?>
                    <div class="info-short-content <?php if ($is_keno): ?> info-short-content-keno <?php endif; ?>">
                        <table class="table table-short">
                            <thead>
                            <tr>
                                <th><?= _('Country') ?></th>
                                <th><?= _('Schedule') ?></th>
                                <th><?= _('Guess Range') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="table-short-country"><span
                                            class="mobile-only-label"><?= _('Country') ?></span>
                                    <i class="sprite-lang sprite-lang-<?= Lotto_View::map_flags($lottery['country']); ?>"></i><?= Security::htmlentities(_($lottery['country'])); ?>
                                </td><?php
                                ?>
                                <td>
                                    <span class="mobile-only-label"><?= _('Schedule') ?></span>
                                    <?php
                                    $timezoneOffset = Carbon::now($lottery['timezone'])->offsetHours;
                                    if ($is_not_keno) {
                                        if ($lottery['slug'] === Lottery::EUROMILLIONS_SUPERDRAW_SLUG):
                                            echo '<div>';
                                            printf(_('Draws take place at 18:00 GMT+%s on Fridays once a considerable jackpot has accumulated.'), $timezoneOffset);
                                            echo '</div>';
                                        else:
                                            echo '<div id="content-lotteries-draw-dates">';
                                            $draw_dates = json_decode($lottery['draw_dates']);
                                            foreach ($draw_dates as $draw_date) {
                                                echo Lotto_View::format_single_day_with_hour(
                                                    Carbon::createFromTimeString($draw_date, $lottery['timezone']),
                                                    new DateTimeZone($lottery['timezone']),
                                                    new DateTimeZone(Lotto_View::get_user_timezone())
                                                );
                                                echo "<br>";
                                            }
                                            echo '</div>';
                                        endif;
                                    } else {
                                        $drawDates = json_decode($lottery['draw_dates']);
                                        $drawInterval = Carbon::parse($drawDates[1])->diffInMinutes(Carbon::parse($drawDates[0]));
                                        $drawDateStart = Carbon::createFromTimeString($drawDates[0], $lottery['timezone'])->format('H:i');
                                        $drawDateEnd = Carbon::createFromTimeString(end($drawDates), $lottery['timezone'])->format('H:i \G\M\TO');
                                        
                                        if ($drawInterval < 60) {
                                            $drawIntervalString = ' ' . $drawInterval . ' minutes';
                                        } else {
                                            $drawInterval = Carbon::parse($drawDates[1])->diffInHours(Carbon::parse($drawDates[0]));
                                            $drawIntervalString = ' ' . $drawInterval . ' hours';
                                        }

                                        echo '<div id="content-lotteries-draw-dates">';
                                        switch ($lottery['slug']):
                                            case Lottery::BELGIAN_KENO_SLUG:
                                                echo _('Draws take place at 20:00 on Monday, Tuesday, Thursday, and Friday, at 19:00 on Wednesday and Saturday, and at 13:00 on Sunday GMT+2.');
                                                break;
                                            case Lottery::DANISH_KENO_SLUG:
                                                echo _('Draws occur daily at 21:30 GMT+') . $timezoneOffset;
                                                break;
                                            case Lottery::FINNISH_KENO_SLUG:
                                                echo _('Draws take place every day at 15:00, 20:58 and 23:00 GMT+3');
                                                break;
                                            case Lottery::FRENCH_KENO_SLUG:
                                                echo _('Draws occur daily at 13:00 and 20:00, GMT+') . $timezoneOffset;
                                                break;
                                            case Lottery::HUNGARIAN_KENO_SLUG:
                                                echo _('Draws occur daily at 20:00, and at 16:45 on Sundays, GMT+2.');
                                                break;
                                            case Lottery::NORWEGIAN_KENO_SLUG:
                                                echo _('Draws occur daily at 20:30 GMT+') . $timezoneOffset;
                                                break;
                                            case Lottery::GERMAN_KENO_SLUG:
                                            case Lottery::SLOVAK_KENO_10_SLUG:
                                            case Lottery::UKRAINIAN_KENO_SLUG:
                                                printf(_('Draws occur daily at %s GMT%s'), $drawDateStart, $timezoneOffset >= 0 ? "+{$timezoneOffset}" : $timezoneOffset);
                                                break;
                                            case Lottery::KENO_NEW_YORK_SLUG:
                                                echo _("Draws take place every 4 minutes, with a break from 3:30 to 4:00, GMT{$timezoneOffset}");
                                                break;
                                            case Lottery::SWEDISH_KENO_SLUG:
                                                echo _("Draws take place daily at 19:00, and at 18:00 on Saturdays and Sundays, GMT+{$timezoneOffset}");
                                                break;
                                            default:
                                                echo str_replace(
                                                    [' 4 minutes', '00:00', '23:59 GMT+2'],
                                                    [$drawIntervalString, $drawDateStart, $drawDateEnd],
                                                    _('Draws take place every 4 minutes between 00:00 &ndash; 23:59 GMT+2')
                                                );
                                        endswitch;
                                        echo '</div>';
                                    }
                                    ?>
                                    </div>
                                </td><?php
                                ?>
                                <td>
                                    <span class="mobile-only-label"><?= _('Guess Range') ?></span>
                                    <?= $guess_range_text; ?>
                                </td>
                            </tr>
                            <?php if (!is_null($lottery['last_numbers'])): ?>
                                <tr>
                                    <td class="table-short-line" colspan="3">
                                        <span class="table-short-label"><?= _('Latest Result') ?>
                                            <span data-type="content-lotteries-last-draw-text">
                                            <?php if (!is_null($lottery['last_date_local'])): ?>
                                                (<?= $last_date_local_text; ?>)<?php endif; ?>: </span></span>
                                        <span class="mobile-only-label"><?= _('Latest Result'); ?>
                                            (<?= $last_date_local_text; ?>)</span>
                                        <span id="content-lotteries-last-result-numbers">
                                            <?= $line_formatted_text; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                    /* end of moved */
                endif;
                ?>
                <section class="page-content page-content-more">
                    <article class="page">
                        <?= apply_filters('the_content', $post_data['extended']); ?>
                    </article>
                </section>
                <?php
                /* moved */
                if (!empty($lottery) && is_array($lottery) && $is_not_keno):
                    ?>
                    <div class="info-detailed-content">
                        <?php
                        if (isset($lottery_types)):
                            ?>
                            <table class="table table-info-detailed">
                                <thead>
                                <tr>
                                    <th class="text-left"><?= _('Tier') ?></th>
                                    <th class="text-left"><?php echo sprintf(Security::htmlentities(_("Match %s")), '<div class="ticket-line-number">' . Security::htmlentities(_('X')) . '</div>'); ?>
                                        <?php if ($lottery_type['bcount'] > 0 || $lottery_type['bextra'] > 0): ?>
                                            +
                                            <?php echo sprintf("%s", '<div class="ticket-line-bnumber">' . Security::htmlentities(_('X')) . '</div>'); ?>
                                        <?php endif; ?>
                                        <?php if ($lottery_type['additional_data']): ?>
                                            <?php echo sprintf("%s", '<div class="ticket-line-bnumber">' . Security::htmlentities(_('R')) . '</div>');
                                        endif; ?>
                                    </th>
                                    <th><?= _('Prize') ?></th>
                                    <th class="text-right"><?= _('Chance to win') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($lottery_types as $key => $item):
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="mobile-only-label"><?php echo Security::htmlentities(sprintf(_("Prize #%d"), $key + 1)); ?></span><span
                                                    class="mobile-hide"><?php echo Security::htmlentities(Lotto_View::romanic_number($key + 1)); ?></span>
                                        </td>
                                        <td class="text-left"><span
                                                    class="mobile-only-label"><?php echo sprintf(Security::htmlentities(_("Match %s")), '<div class="ticket-line-number ticket-line-number-small">' . Security::htmlentities(_('X')) . '</div>'); ?>
                                                <?php if ($lottery_type['bextra'] == 0 || ($lottery_type['bextra'] > 0 && $item['match_b'] > 0)): ?>
                                                    +
                                                    <?php echo sprintf("%s", '<div class="ticket-line-bnumber ticket-line-number-small">' . Security::htmlentities(_('X')) . '</div>'); ?>
                                                <?php endif; ?>: </span>
                                            <?php if (isset($lottery_type_additional_data['refund']) && $item['match_n'] == 0 && $lottery_type_additional_data['refund'] == 1): echo Security::htmlentities(_('R'));
                                            else: echo Security::htmlentities($item['match_n']); endif; ?>

                                            <?php if ($lottery_type['additional_data']): $item_additional = unserialize($item['additional_data']); ?>
                                                <?php if (isset($item_additional['refund']) && $item_additional['refund'] == 1 && $item['match_n'] > 0): ?>
                                                    +
                                                    <?php echo Security::htmlentities(_('R')); ?>
                                                <?php elseif (isset($item_additional['super']) && $item_additional['super'] == 1 && $item['match_n'] > 0): //TODO: refactor ?>
                                                    +
                                                    <?php echo Security::htmlentities(_('S')); ?>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php if ($lottery_type['bcount'] > 0 || $lottery_type['bextra'] > 0): ?>
                                            <?php
                                            if ($lottery_type['bextra'] == 0 ||
                                                ($lottery_type['bextra'] > 0 && $item['match_b'])):
                                                echo Security::htmlentities("+");
                                            endif;
                                            ?>
                                            <?php
                                            if ($lottery_type['bextra'] == 0 ||
                                                ($lottery_type['bextra'] > 0 && $item['match_b'])):
                                                echo Security::htmlentities($item['match_b']);
                                            endif;
                                            ?>
                                        </td>
                                        <?php endif; ?>
                                        <td class="text-center table-info-detailed-prize"><span
                                                    class="mobile-unbold mobile-only-label"><?= _('Prize'); ?>
                                                            :</span> <?php if ($item['type'] == 2):
                                                echo '<span class="table-info-detailed-prize-bold">' . _('Free Quick Pick') . '</span>';
                                            elseif ($item['type'] == 1):
                                                echo '<span class="table-info-detailed-prize-bold">' . Security::htmlentities(sprintf(_("%s shared"), Lotto_View::format_percentage($item['prize'])));
                                                /* special case for EuroMillions */
                                                if ($lottery['id'] == 6 && $item['is_jackpot']) {
                                                    echo ' (' . _("or") . ' ' . Lotto_View::format_percentage("0.27") . ')';
                                                }
                                                echo '</span>';
                                                if ($key == 0):
                                                    echo '<br><span class="mobile-unbold"><span class="table-info-detailed-prize-amount">' . _('Jackpot') . '</span></span>';
                                                elseif ($item['estimated'] != 0):
                                                    echo '<br><span class="mobile-unbold">' . _('Estimated') . ':</span> <span class="table-info-detailed-prize-amount" data-type="content-lotteries-estimated-jackpot-tier-' .  $key + 1 . '">' . Security::htmlentities(Lotto_View::format_currency($item['estimated'], $lottery['currency'], true, null, 1, true)) . '</span>';
                                                endif;
                                            else:
                                                if ($item['prize'] == 0):
                                                    echo '<span class="table-info-detailed-prize-bold"><span class="table-info-detailed-prize-amount">' . _('Jackpot') . '</span></span>';
                                                else:
                                                    echo '<span class="table-info-detailed-prize-bold"><span class="table-info-detailed-prize-amount">' . Security::htmlentities(Lotto_View::format_currency($item['prize'], $lottery['currency'], true, null, 1, true)) . '</span></span>';
                                                endif;
                                            endif; ?></td>
                                        <td class="text-right"><span
                                                    class="mobile-unbold mobile-only-label"><?= _('Chance to win') ?>
                                                            :</span> <?php echo Security::htmlentities(sprintf(_("1 in %s"), Lotto_View::format_number($item['odds']))); ?>
                                        </td>
                                    </tr>
                                <?php
                                endforeach;
                                ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="3"
                                        class="text-right"><?= _('Overall chances of winning any prize') ?>
                                        :
                                    </td>
                                    <td class="text-right"><?php echo Security::htmlentities(sprintf(_("1 in %s"), Lotto_View::format_number($lottery_type['odds']))); ?></td>
                                </tr>
                                </tfoot>
                            </table>
                        <?php
                        else:
                            // TODO: message error
                        endif;
                        ?>
                    </div>
                <?php
                    /* end of moved */

                endif;
                ?>
            </div>

            <?php if (is_active_sidebar('lottery-info-content-sidebar-id')):?>
                <div class="content-box-sidebar mobile-hide">
                    <?php Lotto_Helper::widget_before_area('lottery-info-content-sidebar-id'); ?>
                    <?php dynamic_sidebar('lottery-info-content-sidebar-id'); ?>
                    <?php Lotto_Helper::widget_after_area('lottery-info-content-sidebar-id'); ?>
                </div>
            <?php endif;?>

            <div class="content-box-main content-box-full">
                <?php 
                if (isset($lottery) && $is_keno) {
                    include('partials/keno-prize-breakdown-table.php');
                }
                ?>
            </div>

            <div class="clearfix"></div>
            <?php

            base_theme_social_share_bottom(
                $social_share_rows,
                $counter_socials,
                $current_url
            );
            ?>
        </div>
    </div>
    <?php
    get_active_sidebar('lottery-info-more-sidebar-id');
    ?>
</div>