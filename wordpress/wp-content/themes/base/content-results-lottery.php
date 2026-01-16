<?php

use Carbon\Carbon;
use Helpers\AssetHelper;
use Services\LotteryAdditionalDataService;

if (!defined('WPINC')) {
    die;
}

if (empty($title)) {
    $title = '';
}

if (isset($args) && key_exists('post', $args)) {
    $post = $args['post'];
    $drawDate = $args['date'];
    $lotteryDraws = $args['lottery_draws'];
}

$lottery = lotto_platform_get_lottery_by_slug($post->post_name);
$locale = explode('_', get_locale());
$language = $locale[0];

list($socialShareRows, $counterSocials, $currentUrl) = 
    Helpers_General::get_prepared_social_share_links();

$lotteryTimezone = '';
if (isset($drawDate)) {
    $drawDateWithTimezone = Lotto_View::format_date(
            $drawDate,
            timezonein: $lottery['timezone'],
            use_user_timezone: false
    );
    /**
     * There isn`t a better option to take timezone stamp like this GMT-04, EDT, CEST, CET.
     * Carbon and dateTime format returns timezone like -04 without GMT word (expected GMT-04).
     */
    $drawDateWithTimezone = explode(' ', $drawDateWithTimezone);
    $lotteryTimezone = end($drawDateWithTimezone);
    $lotteryTimezone = " ($lotteryTimezone)";
}

?>
<div class="content-area<?= Lotto_Helper::get_widget_main_area_classes(null, 'lottery-results-more-sidebar-id') ?>">
    <?php
    // These are settings important for showing lottery submenu properly
    $category = null;
    $show_main_width_div = true;
    $relative_class_value = '';
    $selected_class_values = [
        0 => false,  // Play submenu
        1 => true,   // Results submenu
        2 => false,  // Information submenu
        3 => false,  // News submenu
    ];

    include('box/lottery/submenu.php');
    get_active_sidebar('lottery-results-sidebar-id');
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <div class="main-width content-width">
        <div class="content-box<?php
        echo Lotto_Helper::get_widget_top_area_classes('lottery-results-sidebar-id');
        echo Lotto_Helper::get_widget_bottom_area_classes('lottery-results-more-sidebar-id');
        ?>">
            <?php
            $lotteryTypeAdditionalData = null;
            $isKeno = false;

            if (!empty($lottery) && is_array($lottery)) {
                /** moved **/
                $whitelabel = Lotto_Settings::getInstance()->get('whitelabel');
                $isKeno = Helpers_Lottery::is_keno($lottery);

                // We get here only enabled lotteries
                $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
                $lottery = null;
                $lotteryDraws = null;
                $prev = null;
                $next = null;
                $lotteryDraw = null;
                if (empty($drawDate)) {
                    $drawDate = Lotto_Settings::getInstance()->get('results_date');
                }

                $drawData = null;
                $lottery_type = null;
                $lotteryAdditionalData = null;

                if (isset($post) && isset($lotteries['__by_slug'][$post->post_name])) {
                    $lottery = $lotteries['__by_slug'][$post->post_name];
                    $drawDate = Carbon::parse($drawDate, $lottery['timezone']);

                    if (empty($lotteryDraws)) {
                        $lotteryDraws = Model_Lottery_Draw::get_draw_list_by_lottery($lottery, $drawDate->format(Helpers_Time::DATE_FORMAT));
                    }

                    if (!empty($lotteryDraws) && count($lotteryDraws) > 0) {
                        $date = Lotto_Settings::getInstance()->get('results_date');
                        $prev = Lotto_Settings::getInstance()->get('results_prev');
                        $next = Lotto_Settings::getInstance()->get('results_next');

                        $lotteryDraw = Model_Lottery_Draw::get_draw_by_date($lottery, $drawDate);
                        $drawData = Model_Lottery_Prize_Data::get_draw_prize_data($lotteryDraw);
                        $lottery_type = Model_Lottery_Type::get_lottery_type_for_date($lottery, $drawDate);

                        $lotteryAdditionalData = null;
                        if ($lottery['additional_data']) {
                            $lotteryAdditionalData = unserialize($lotteryDraw['additional_data']);
                        }

                        if ($lotteryAdditionalData === false) {
                            $lotteryAdditionalData = null;
                        }

                        if ($lottery_type['additional_data']) {
                            $lotteryTypeAdditionalData = unserialize($lottery_type['additional_data']);
                        }
                    }
                }
            }
            $isNotKeno = !$isKeno;

            $addToTitleText = '';
            if (!empty($drawDate)) {
                $dateText = Lotto_View::format_date_without_timezone(
                    $drawDate->setTimezone('UTC')->format('Y-m-d H:i')
                );
                $addToTitleText = ' - ' . $dateText;
            }
            ?>

            <section class="page-content">
                <article class="page">
                    <script>
                        window.lotteryPageTitle = "<?php echo esc_html(get_the_title());?>";
                        window.lotteryChooseText = '<?= _('Choose...') ?>';
                    </script>
                    <h1 id="lotteryPageTitle"><?php echo esc_html(get_the_title(null, $addToTitleText));?></h1>
                </article>
            </section>

            <?php if (is_active_sidebar('lottery-results-content-sidebar-id')): ?>
                <div class="content-box-main <?= $isKeno ? 'content-box-full' : '' ?>">
            <?php endif; ?>

            <?php
                $page_name = 'results';
                include('box/lottery/group.php');
            ?>
                </article>
            </section>
            <?php
                if (!empty($lottery) && is_array($lottery)) :
                    ?>
                    <div class="results-short-content <?= $isKeno ? 'results-short-content-keno' : '' ?>">
                        <?php if ($isKeno && !empty($lotteryDraw)): ?>
                            <div class="results-draw-number">
                                <label><?= _('Draw number') ?>:</label>
                                <span id="lotteryDrawNumber"><?= $lotteryDraw['draw_no'] ?></span>
                            </div>
                            <div class="clearfix"></div>
                        <?php endif; ?>
                        <?php if (!empty($lotteryDraws) && count($lotteryDraws) > 0): ?>
                            <nav class="datetime-dropdown-container <?= $isKeno ? '': 'dropdown-end' ?> pull-right">
                                <div class="datetime-dropdown" id="date-select">
                                    <div class="datetime-dropdown-header">
                                        <div class="datetime-dropdown-header-icon">
                                            <img
                                                src="<?= AssetHelper::mix('images/icon-calendar.png', AssetHelper::TYPE_WORDPRESS, true) ?>"
                                                alt="Calendar Icon"
                                            />
                                        </div>
                                        <div class="datetime-dropdown-header-placeholder">
                                            <div class="datetime-dropdown-header-label">
                                                <?= _('Draw date') . ($isKeno ? '' : $lotteryTimezone) ?>
                                            </div>
                                            <div class="datetime-dropdown-header-value" id="selectedDate">
                                                <span class="loading"></span>
                                                <!-- Draw date will be generated automatically by JS -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                    if ($isKeno) {
                                ?>
                                <div class="datetime-dropdown" id="time-select">
                                    <div class="datetime-dropdown-header">
                                        <div class="datetime-dropdown-header-icon">
                                            <img
                                                src="<?= AssetHelper::mix('images/icon-clock.png', AssetHelper::TYPE_WORDPRESS, true) ?>"
                                                alt="Clock Icon"
                                            />
                                        </div>
                                        <div class="datetime-dropdown-header-placeholder">
                                            <div class="datetime-dropdown-header-label">
                                                <?= _('Draw time') . $lotteryTimezone ?>
                                            </div>
                                            <div class="datetime-dropdown-header-value">
                                                <?= _('Choose...') ?>
                                                <!-- Draw date will be generated automatically by JS -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="datetime-dropdown-menu"></div>
                                </div>
                                <?php } ?>
                            </nav>
                            <div class="pull-left results-short-line" data-type="content-lotteries-last-result-numbers">
                                <?php
                                if (isset($lotteryDraw)) :
                                    echo wp_kses(Lotto_View::format_line($lotteryDraw['numbers'], $lotteryDraw['bnumbers'], null, null, null, $lotteryAdditionalData), array('div' => array('class' => array(), 'data-tooltip' => array()), 'span' => array()));
                                endif;
                                ?>
                            </div>
                            <?php if ($lotteryDraw['jackpot'] != 0): ?>
                                <div class="pull-left results-short-jackpot">
                                    <?= _('Estimated jackpot') ?>:
                                    <span id="estimatedJackpotValue">
                                        <?= Lotto_View::format_currency($lotteryDraw['jackpot'] * 1000000, $lottery['currency']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                        </div>

                            <?php if ($isKeno):?>
                                <div class="custom-widget-box">
                                    <?php the_widget('Lotto_Widget_Small_Draw', ['inline' => true]);?>
                                </div>
                            <?php endif;?>

                        <div class="results-short-content <?= $isKeno ? 'results-short-content-keno' : '' ?>">
                            <?php
                                if ($isKeno) {
                                    include('partials/keno-prize-breakdown-table.php');
                                }
                            ?>
                            <div class="clearfix"></div>
                            <?php
                                else:
                            ?>
                            <div class="platform-alert platform-alert-info">
                                <p>
                                    <span class="fa fa-exclamation-circle"></span>
                                    <?= _('There are no results available for this lottery yet.') ?>
                                </p>
                            </div>
                            <?php
                        endif;
                        ?>
                    </div>
                    
                    <?php if ($isNotKeno):?>
                        <div class="results-mobile-show">
                            <?php the_widget('Lotto_Widget_Small_Draw', ['inline' => true]); ?>
                        </div>
                    <?php endif;?>
                    
                    <?php if (!in_array($lottery['slug'], ['gg-world', 'gg-world-x', 'gg-world-million']) && $isNotKeno) : ?>
                    <div class="results-detailed-content" id="lotteryResultContainer">
                        <?= $title; ?>
                        <?php if (isset($drawData) && count($drawData) > 0) : ?>
                            <table class="table table-results-detailed" data-currency="<?= $lottery['currency'] ?>">
                                <thead>
                                <tr>
                                    <th class="text-left"><?= _('Tier') ?></th>
                                    <th class="text-left">
                                        <?= sprintf(_("Match %s"), '<div class="ticket-line-number">' . _('X') . '</div>') ?>
                                        <?php if ($lottery_type['bcount'] > 0 || $lottery_type['bextra'] > 0): ?>
                                            +
                                            <?= sprintf("%s", '<div class="ticket-line-bnumber">' . _('X') . '</div>') ?>
                                        <?php endif; ?>
                                        <?php if ($lottery_type['additional_data']) :
                                            $lotteryTypeAdditionalData = unserialize($lottery_type['additional_data']);
                                            /** @var LotteryAdditionalDataService $lineServices */
                                            $lineServices = Container::get(LotteryAdditionalDataService::class);
                                            $ballShortname = $lineServices->getBallShortName($lotteryTypeAdditionalData);;
                                            ?>
                                            <?php if ($lottery_type['bextra'] > 0) :
                                                ?> + <?php
                                            endif; ?>
                                            <?= sprintf("%s", '<div class="ticket-line-bnumber">' . _($ballShortname) . '</div>');
                                        endif; ?>
                                    </th>
                                    <th><?= _('Winners') ?></th>
                                    <th class="text-right"><?= _('Payout per winner') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($drawData as $key => $item) :
                                    ?>
                                    <tr
                                    <?php if (Helpers_Lottery::supports_ticket_multipliers_by_lottery_id($lottery['id'])): ?>
                                        data-multiplier="<?= $item['multiplier'] ?>"
                                    <?php endif; ?>
                                    >
                                        <td class="text-left">
                                            <span class="mobile-only-label">
                                                <?= sprintf(_("Prize #%d"), $key + 1) ?>
                                            </span>
                                            <span class="mobile-hide">
                                                <?= Lotto_View::romanic_number($key + 1) ?>
                                            </span>
                                        </td>
                                        <td class="text-left">
                                            <span class="mobile-only-label">
                                                <?= sprintf(_("Match %s"), '<div class="ticket-line-number ticket-line-number-small">' . _('X') . '</div>');
                                                if (
                                                    $lottery_type['bextra'] == 0 ||
                                                    ($lottery_type['bextra'] > 0 && $item['match_b'])
                                                ):
                                                    echo '+&nbsp;';
                                                    echo sprintf("%s", '<div class="ticket-line-bnumber ticket-line-number-small">' . _('X') . '</div>');
                                                endif;
                                                ?>: 
                                            </span>
                                            <?php
                                            $isRefund = isset($lotteryTypeAdditionalData['refund']) && $lotteryTypeAdditionalData['refund'] == 1;
                                            if ($item['match_n'] == 0 && $isRefund):
                                                echo _('R');
                                            else:
                                                echo $item['match_n'];
                                            endif;

                                            if ($lottery_type['additional_data']) :
                                                $itemAdditional = unserialize($item['additional_data']);
                                                if (
                                                    isset($itemAdditional['refund']) && //TODO:refactor
                                                    $itemAdditional['refund'] == 1 &&
                                                    $item['match_n'] > 0
                                                ):
                                                    echo '+&nbsp;';
                                                    echo _('R');
                                                elseif (
                                                    isset($itemAdditional['super']) &&
                                                    $itemAdditional['super'] == 1 &&
                                                    $item['match_n'] > 0
                                                ) :
                                                    echo '+&nbsp;';
                                                    echo _('S');
                                                endif;
                                            endif;

                                            if ($lottery_type['bcount'] > 0 || $lottery_type['bextra'] > 0):
                                                if (
                                                    $lottery_type['bextra'] == 0 ||
                                                    ($lottery_type['bextra'] > 0 && $item['match_b'])
                                                ):
                                                    echo '+&nbsp;';
                                                    echo $item['match_b'];
                                                endif;
                                            endif;
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="mobile-unbold mobile-only-label">
                                                <?= _('Winners') ?>:
                                            </span>
                                            <?= '&nbsp;' ?>
                                            <span class="table-results-detailed-winners">
                                                <?= Lotto_View::format_number($item['winners']) ?>
                                            </span>
                                        </td>
                                        <td class="text-right table-results-detailed-jackpot">
                                            <span class="mobile-unbold mobile-only-label">
                                                <?= _('Payout per winner') ?>:
                                            </span>
                                            <?= '&nbsp;'; ?>
                                            <span class="table-results-detailed-amount">
                                                <?= $item['type'] == 2 ? _('Free Quick Pick') : Lotto_View::format_currency($item['prizes'], $lottery['currency'], true) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                endforeach;
                                ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="2" class="text-left">
                                        <?= _('Total Sum') ?>:
                                    </td>
                                    <td class="text-center">
                                        <span class="mobile-only-label">
                                            <?= _('Total Winners') ?>:
                                        </span>
                                        <?= '&nbsp;' ?>
                                        <span class="table-results-detailed-winners">
                                            <?= Lotto_View::format_number($lotteryDraw['total_winners']) ?>
                                        </span>
                                    </td>
                                    <td class="text-right table-results-detailed-jackpot">
                                        <span class="mobile-only-label">
                                            <? _('Total Prize') ?>:
                                        </span>
                                        <?= '&nbsp;'; ?>
                                        <span class="table-results-detailed-amount">
                                            <?= Lotto_View::format_currency($lotteryDraw['total_prize'], $lottery['currency'], true) ?>
                                        </span>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                            <?php
                        else :
                            // TODO: message error
                        endif;
                        ?>
                    </div>
                    <?php endif; ?>
                    <?php
                    /* end of moved */
                endif;

                ?>
                <section class="page-content">
                    <article class="page">
                        <?php
                            $postData = get_extended($post->post_content);
                            echo apply_filters('the_content', $postData['main']);
                        ?>
                    </article>
                </section>
                <section class="page-content page-content-more">
                    <article class="page">
                        <?= apply_filters('the_content', $postData['extended']) ?>
                    </article>
                </section>
                <?php if (is_active_sidebar('lottery-results-content-sidebar-id')): ?>
                </div>
                <?php if ($isNotKeno && !empty($lottery)): ?>
                    <div class="content-box-sidebar">
                        <?php
                            Lotto_Helper::widget_before_area('lottery-results-content-sidebar-id');
                            dynamic_sidebar('lottery-results-content-sidebar-id');
                            Lotto_Helper::widget_after_area('lottery-results-content-sidebar-id');
                        ?>
                    </div>
                <?php endif; ?>
                <div class="clearfix"></div>
                <?php 
                    endif;
                    base_theme_social_share_bottom(
                        $socialShareRows,
                        $counterSocials,
                        $currentUrl
                    );
                ?>
        </div>
    </div>
    <?php get_active_sidebar('lottery-results-more-sidebar-id') ?>
</div>