<?php

use Helpers\SanitizerHelper;
use Models\Raffle;
use Fuel\Core\Date;
use Fuel\Core\Input;
use Helpers\AssetHelper;
use Orm\RecordNotFound;
use Models\RaffleRuleTier;
use Modules\Account\Reward\PrizeType;
use Models\RafflePrize;
use Models\RaffleDraw;

$show_404 = function () {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
};

$jsRafflePath = AssetHelper::mix('js/raffle.min.js', AssetHelper::TYPE_WORDPRESS);
wp_enqueue_script('raffle-scripts', $jsRafflePath, [], false, true);

                // ---------------------- DEPENDENCIES ----------------------
                /** @var Raffle $raffle_dao */
                $raffle_dao = Container::get(Raffle::class);
                /** @var RafflePrize $prize_dao */
                $prize_dao = Container::get(RafflePrize::class);
                /** @var RaffleDraw $draw_dao */
                $draw_dao = Container::get(RaffleDraw::class);

// ---------------------- VARS ----------------------
if (key_exists('post', $args)) {
    $post = $args['post'];
}

try {
    $raffle = $raffle_dao->get_by_slug_with_currency_and_rule($post->post_name);
} catch (RecordNotFound $exception) {
    $show_404();
}

$draw_id = (int)SanitizerHelper::sanitizeString(Input::get('draw_id') ?? '');
if (!empty($draw_id) && !$draw_dao->exists($draw_id)) {
    $show_404();
}

/** @var array|RaffleDraw[] $draws */
$draws = $draw_dao->get_draws_by_raffle($raffle->id);

if (empty($draw_id) && !empty($draws)) {
    $first_draw = reset($draws);
    $draw_id = $first_draw->id;
} elseif (!empty($draw_id) && !empty($draws)) {
    /** @var RaffleDraw $draw */
    foreach ($draws as $draw) {
        if ($draw->id === $draw_id) {
            $first_draw = $draw;
        }
    }
}

$locale = explode('_', get_locale());
$language = $locale[0];
// ---------------------- WINNING TICKETS ----------------------
$prizes = $prize_dao->get_prizes_by_draw($draw_id);
$main_prizes = $prize_dao->limit_to_tier_main_prize()->get_prizes_by_draw($draw_id);

// ---------------------- UTILS ----------------------
$format_date = function (Date $full_date) use ($raffle) {
    return Lotto_View::format_date_without_timezone($full_date->format('mysql'), IntlDateFormatter::LONG, IntlDateFormatter::SHORT, null, $raffle->timezone, $raffle->timezone);
};

$format_number = function (int $number) use ($raffle) {
    return str_pad($number, strlen($raffle->getFirstRule()->max_lines_per_draw), "0", STR_PAD_LEFT);
};

$is_option_selected = function (int $option_draw_id) use ($draw_id) {
    return $option_draw_id === $draw_id;
};

$is_prize_in_tickets = function (RaffleRuleTier $tier) {
    $tier_prize_in_kind = $tier->tier_prize_in_kind;
    return !empty($tier_prize_in_kind) && $tier_prize_in_kind->type === PrizeType::TICKET;
};

$is_prize_in_kind = function (RaffleRuleTier $tier) {
    $tier_prize_in_kind = $tier->tier_prize_in_kind;
    return !empty($tier_prize_in_kind);
};

?>
<div class="content-area<?= Lotto_Helper::get_widget_main_area_classes(0, 'lottery-results-more-sidebar-id') ?>">
    <?php
    // These are settings important for showing lottery submenu properly
    $category = null;
    $show_main_width_div = true;
    $relative_class_value = '';
    $selected_class_values = [
        0 => false, // Play submenu
        1 => true, // Results submenu
        2 => false, // Information submenu
        3 => false, // News submenu
    ];

    include('box/raffle/submenu.php');

    get_active_sidebar('lottery-results-sidebar-id');
    ?>
    <div class="main-width content-width">
        <div class="content-box">
            <section class="page-content">
                <article class="page">
                    <h1><?php echo get_the_title();?></h1>
                </article>
            </section>
            <div class="content-box-main">
                <?php if ($raffle->is_turned_on): ?>
                <?php if (!empty($draws)): ?>
                    <div class="results-short-content results-short-content-raffle">
                        <div class="pull-left results-short-jackpot results-short-jackpot-raffle">
                            <?= _('Main prize'); ?>:
                            <span>
                                <?= $is_prize_in_tickets(current($raffle->getFirstRule()->tiers)) ? current($raffle->getFirstRule()->tiers)->tier_prize_in_kind->name : Lotto_View::format_currency($raffle->main_prize, $raffle->currency->code); ?>
                            </span>
                        </div>

                        <div class="clearfix"></div>
                        <?php // ---------------------- DATES OPTIONS INPUT ----------------------?>
                        <div class="results-short-select-wrapper">
                            <select
                                    name="results[date]"
                                    class="results-short-select"
                                    id="raffleDateSelect"
                                    data-lottery-name="<?= htmlspecialchars($post->post_name); ?>"
                                    data-language="<?= $language ?>"
                                    data-raffle="true"
                            >
                                <?php foreach ($draws as $date_option): ?>
                                    <option<?php if ($is_option_selected($date_option->id)): ?> selected<?php endif; ?> value="<?= $date_option->id ?>">
                                        <?= $format_date($date_option->date) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php // ---------------------- DATES OPTIONS INPUT ----------------------?>

                        <div class="pull-left raffle-winning-ticket">
                            <?= _('Draw number') ?>: <span id="raffleDrawNumber"><b><?= $first_draw->draw_no ?></b>(<?= $format_date($first_draw->date) ?>)</span>
                        </div>

                    </div>
                <?php endif; ?>

                <?php // ---------------------- MAX PRIZE WINNING TICKETS ----------------------?>

                <?php if (!empty($draws)): ?>
                    <div class="clearfix"></div>

                    <div class="raffle-winning-ticket">
                        <?= _('Winning tickets (main prize)'); ?>:
                        <div class="widget-chosen-tickets-container" id="raffleWinningTicketsPrizes">
                            <?php foreach ($main_prizes as $prize): ?>
                                <div class="widget-chosen-ticket">
                                    <?php $lines = $prize->lines ?>
                                    <?php foreach ($lines as $line): ?>
                                        <?= $format_number($line->number) ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php // ---------------------- MAX PRIZE WINNING TICKETS ----------------------?>

                <div class="results-detailed-content">
                    <?php if (empty($draws)): ?>
                        <?= _('No results found.') ?>
                    <?php else: ?>
                    <table class="table table-results-detailed-raffle">
                        <thead>
                        <tr>
                            <th class="text-center"><?= _('Tier'); ?></th>
                            <th class="text-center"><?= _('Winners'); ?></th>
                            <th class="text-center"><?= _('Prize'); ?></th>
                            <th class="text-center"><?= _('Winners ticket'); ?></th>
                        </tr>
                        </thead>
                        <tbody class="js-fix-table" id="raffleTableContent">
                        <?php $counter = 1; ?>
                        <?php foreach ($prizes as $prize): ?>
                            <tr>
                                <td class="text-center">
                                    <?= Lotto_View::romanic_number($counter++) ?>
                                </td>
                                <td class="text-center">
                                    <?= $prize->lines_won_count ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                        switch (true) {
                                            case $is_prize_in_tickets($prize->tier):
                                                echo $prize->tier->tier_prize_in_kind->name;
                                                break;
                                            case $is_prize_in_kind($prize->tier):
                                                echo Lotto_View::format_currency($prize->prize_amount, $prize->currency->code, true);
                                                echo !empty($tier_prize_in_kind) ? ' (' . $prize->tier->tier_prize_in_kind->name . ')' : '';
                                                break;
                                            default:
                                                echo Lotto_View::format_currency($prize->prize_amount, $prize->currency->code, true);
                                        }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a data-tier="<?= $prize->id ?>" href="javascript:void(0)" class="raffle-show-results"><?= _('Show tickets'); ?></a>
                                </td>
                            </tr>
                            <tr data-tier="<?= $prize->id ?>" class="hidden">
                                <td colspan="4">
                                    <div class="widget-ticket-numbers">
                                        <?php $lines = $prize->lines; ?>
                                        <?php foreach ($lines as $line): ?>
                                            <div class="raffle-number" style="padding: 3px; margin-bottom: 3px;"><?= $format_number($line->number) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                <section class="page-content page-content-more">
                    <article class="page">
                        <?php
                        $post_data = get_extended($post->post_content);
                        echo apply_filters('the_content', $post_data['main']);
                        ?>
                    </article>
                </section>
            </div>
            <div class="content-box-sidebar">
                <?php
                    the_widget('Lotto_Widget_Sidebar', ['lottery' => 'lottery_gg-world']);
                    the_widget('Lotto_Widget_Sidebar', ['lottery' => 'lottery_gg-world-keno']);

                    // to add widgets in wordpress
                    // get_active_sidebar('raffle-results-content-sidebar-id');
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
