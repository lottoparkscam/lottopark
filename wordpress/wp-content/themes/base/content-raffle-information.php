<?php

if (!defined('WPINC')) {
    die;
}

use Models\Raffle;
use Models\RaffleRuleTier;
use Modules\Account\Reward\PrizeType;

/** @var Raffle $raffle_dao */
$raffle_dao = Container::get(Raffle::class);
$raffle = $raffle_dao->get_by_slug_with_currency_and_rule($post->post_name);

$content_area_class = Lotto_Helper::get_widget_main_area_classes(null, "lottery-info-more-sidebar-id");
$content_box_classes = Lotto_Helper::get_widget_top_area_classes("lottery-info-sidebar-id") . Lotto_Helper::get_widget_bottom_area_classes("lottery-info-more-sidebar-id");
$post_data = get_extended($post->post_content);

$is_prize_in_tickets = function (RaffleRuleTier $tier) {
    $tier_prize_in_kind = $tier->tier_prize_in_kind;
    return !empty($tier_prize_in_kind) && $tier_prize_in_kind->type === PrizeType::TICKET;
};

$is_prize_in_kind = function (RaffleRuleTier $tier) {
    $tier_prize_in_kind = $tier->tier_prize_in_kind;
    return !empty($tier_prize_in_kind);
};

?>
<div class="content-area<?= $content_area_class ?>">
    <?php
    // These are settings important for showing lottery submenu properly
    $category = null;
    $show_main_width_div = true;
    $relative_class_value = 'relative';
    $selected_class_values = [
        0 => false,              // Play submenu
        1 => false,             // Results submenu
        2 => true,             // Information submenu
        3 => false,             // News submenu
    ];
    include('box/raffle/submenu.php');

    get_active_sidebar('lottery-info-sidebar-id');
    ?>
    <div class="main-width content-width">
        <div class="content-box<?= $content_box_classes ?>">
            <section class="page-content">
                <article class="page">
                    <h1><?php the_title(); ?></h1>
                </article>
            </section>
            <?php if (is_active_sidebar('lottery-info-content-sidebar-id')) : ?>
            <div class="content-box-main">
                <section class="page-content">
                    <article class="page">
                        <?= apply_filters('the_content', $post_data['main']); ?>
                    </article>
                </section>
                <?php endif; ?>
                <?php if ($raffle->is_turned_on): ?>
                <div class="info-short-content info-short-content-raffle">
                    <table class="table table-short table-raffle-info">
                        <thead>
                        <tr>
                            <th><?= _('Country') ?></th>
                            <th><?= _('Tickets Remaining') ?></th>
                            <th><?= _('Main prize') ?></th>
                        </tr>
                        </thead>
                        <tr>
                        <tr>
                            <td style="display: flex; align-items: center">
                                <i class="sprite-lang sprite-lang-<?= Lotto_View::map_flags($raffle->country); ?>"></i>&nbsp;<?= Security::htmlentities(_($raffle->country)); ?>
                            </td>
                            <td>
                                <?php /** This value is set for google bots, real value will be updated in javascript */ ?>
                                <span class="available-numbers-to-update">
                                    <?= Lotto_View::format_number($raffle->getFirstRule()->max_lines_per_draw) ?>
                                </span>/
                                <?= Lotto_View::format_number($raffle->getFirstRule()->max_lines_per_draw) ?>
                            </td>
                            <td>
                                <?= $is_prize_in_tickets(reset($raffle->getFirstRule()->tiers)) ? reset($raffle->getFirstRule()->tiers)->tier_prize_in_kind->name : Lotto_View::format_currency($raffle->main_prize, $raffle->currency->code); ?>
                            </td>
                        </tr>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                <section class="page-content page-content-more">
                    <article class="page">
                        <?= apply_filters('the_content', $post_data['extended']); ?>
                    </article>
                </section>
                <?php if ($raffle->is_turned_on): ?>
                    <div class="info-detailed-content">
                        <table class="table table-info-detailed-raffle">
                            <thead>
                            <tr>
                                <th><?= _('Tier') ?></th>
                                <th><?= _('Winners') ?></th>
                                <th><?= _('Chance to win') ?></th>
                                <th><?= _('Prize') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $c = 1;
                            $total_prize = $total_winners = 0;
                            foreach ($raffle->getFirstRule()->tiers as $tier) :
                                $total_winners += $winners = Helper_Raffle::tier_matches_to_winners($tier->matches);
                                $total_prize += $tier->prize_amount * $winners;
                                ?>
                                <tr>
                                    <td class="text-center"><?= Lotto_View::romanic_number($c++) ?></td>
                                    <td class="text-center"><?= Lotto_View::format_number($winners) ?></td>
                                    <td class="text-center"><?= _('1 to') . ' ' . $tier->odds ?></td>
                                    <td class="text-center">
                                        <?php
                                            switch (true) {
                                                case $is_prize_in_tickets($tier):
                                                    echo $tier->tier_prize_in_kind->name;
                                                    break;
                                                case $is_prize_in_kind($tier):
                                                    echo Lotto_View::format_currency($tier->prize_amount, $raffle->currency->code, true);
                                                    echo !empty($tier->tier_prize_in_kind) ? ' (' . $tier->tier_prize_in_kind->name . ')' : '';
                                                    break;
                                                default:
                                                    echo Lotto_View::format_currency($tier->prize_amount, $raffle->currency->code, true);
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <?php if (!$is_prize_in_tickets(reset($raffle->getFirstRule()->tiers))): ?>
                                <tr>
                                    <td class="text-center"><?= _('Total: ') ?></td>
                                    <td class="text-center"><?= $total_winners ?></td>
                                    <td></td>
                                    <td class="text-center"><?= Security::htmlentities(Lotto_View::format_currency($total_prize, $raffle->currency->code)); ?></td>
                                </tr>
                            <?php endif ?>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="content-box-sidebar">
                <?php
                    the_widget('Lotto_Widget_Sidebar', ['lottery' => 'lottery_gg-world']);
                    the_widget('Lotto_Widget_Sidebar', ['lottery' => 'lottery_gg-world-keno']);

                    // to add widgets in wordpress
                    // get_active_sidebar('raffle-info-content-sidebar-id');
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
