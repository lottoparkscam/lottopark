<?php

if (!defined('WPINC')) {
    die;
}

use Fuel\Core\Config;
use Fuel\Core\Session;
use Fuel\Core\Security;
use Models\RaffleRuleTier;
use Helpers\UrlHelper;
use Modules\Account\Reward\PrizeType;
use Helpers\AssetHelper;
use Models\Raffle;

$jsRafflePath = AssetHelper::mix('js/raffle.min.js', AssetHelper::TYPE_WORDPRESS);
wp_enqueue_script('raffle-scripts', $jsRafflePath, [], false, true);

$whitelabel = Lotto_Settings::getInstance()->get('whitelabel');
$container = Container::forge();

// ---------------------- DEPENDENCIES ----------------------
/** @var Raffle $raffle_dao */
$raffle_dao = $container->get(Raffle::class);

// ---------------------- VARS ----------------------
$raffle_slug = $post->post_name;
$raffle = $raffle_dao->get_by_slug_with_currency_and_rule($raffle_slug);

# wee assumed that first tier is always main prize (suppose to be), then we check it it prize in kind
$main_prize_tier = array_filter($raffle->getFirstRule()->tiers, function (RaffleRuleTier $tier) {
    return $tier->is_main_prize;
});
/** @var RaffleRuleTier $main_prize_tier */
$main_prize_tier = reset($main_prize_tier);

/** @var Services_Currency_Calc $currency_calc */
$currency_calc = $container->get(Services_Currency_Calc::class);

// ---------------------- HELPERS ----------------------
$format_number = function (int $number) use ($raffle) {
    return str_pad($number, strlen($raffle->getFirstRule()->max_lines_per_draw), "0", STR_PAD_LEFT);
};

$raffle_image = Lotto_View::get_lottery_image($raffle->id, $whitelabel, 'raffle');
$max_lines_per_draw = $raffle->getFirstRule()->max_lines_per_draw;

// Now we have only closed raffle so this functionality is not prepared for PageCache
if (isset($raffle['next_draw_date']) && !empty($raffle['next_draw_date'])) {
    $draw_in_human_time = sprintf(
        _("draw in %s"),
        human_time_diff(strtotime($raffle['next_draw_date']))
    );
    $draw_in_human_time_escaped = Security::htmlentities($draw_in_human_time);
}

$is_prize_in_tickets = function (RaffleRuleTier $tier) {
    $tier_prize_in_kind = $tier->tier_prize_in_kind;
    return !empty($tier_prize_in_kind) && $tier_prize_in_kind->type === PrizeType::TICKET;
};

$is_prize_in_kind = function (RaffleRuleTier $tier) {
    $tier_prize_in_kind = $tier->tier_prize_in_kind;
    return !empty($tier_prize_in_kind);
};

//Raffle variables
$line_price = $raffle->getFirstRule()->line_price_with_fee;

// These data will be updated by JS asynchronously in Raffle.js
?>
<div id="raffle-config" class="hidden" data-raffle="<?php echo htmlspecialchars(json_encode(
    [
        'purchase_url' => lotto_platform_get_permalink_by_slug('play-raffle') . $post->post_name . '/purchase',
        'is_logged_in' => false,
        'currency_code' => $raffle->currency->code,
        'line_price' => $line_price,
        'js_currency_format' => '',
        'min_bet' => $raffle->min_bets ? $raffle->min_bets : 1,
        'max_bet' => $raffle->max_bets ? $raffle->max_bets : 7,
        'max_lines_per_draw' => $raffle->getFirstRule()->max_lines_per_draw,
        'taken_numbers' => [],
        'n' => Config::get('security.csrf_token_key'),
        't' => '',

        'user_currency_code' => '',
        'user_line_price' => 0,
        'user_bonus_balance' => 0.0
    ]
), ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="content-area<?php
                        echo Lotto_Helper::get_widget_main_area_classes("play-lottery-sidebar-id", "play-lottery-more-sidebar-id");
                        ?>">
    <div>
        <?php
        // These are settings important for showing lottery submenu properly
        $category = null;
        $show_main_width_div = true;
        $relative_class_value = 'relative';
        $selected_class_values = [
            0 => true,              // Play submenu
            1 => false,             // Results submenu
            2 => false,             // Information submenu
            3 => false,             // News submenu
        ];
        include('box/raffle/submenu.php');
        ?>
        <div class="widget widget_lotto_platform_widget_ticket">
            <div class="main-width content-width">
                <div class="widget-ticket-wrapper content-box">
                    <div id="raffle-is-sold-out-container"></div>
                    <?php if ($error = Session::get_flash('error')): ?>
                    <div class="platform-alert platform-alert-error">
                        <p><i class="fa fa-exclamation-circle"></i> <?= $error ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($info = Session::get_flash('info')): ?>
                        <div class="platform-alert platform-alert-info">
                            <p><i class="fa fa-info-circle"></i> <?= $info ?></p>
                        </div>
                    <?php endif; ?>
                    <div id="alert-max-numbers-count" class="platform-alert platform-alert-warning hidden">
                        <p><i class="fa fa-exclamation-triangle"></i> You can select up to <?= $raffle->max_bets ?> numbers.</p>
                    </div>
                    <div id="alert-must-be-logged_in" class="platform-alert platform-alert-info hidden">
                        <p><i class="fa fa-exclamation-triangle"></i> <?= _('You must be logged in to purchase your tickets.') ?></p>
                    </div>
                    <div id="flashmessages"></div>
                    <div class="widget-ticket-buttons-all pull-right">
                        <?php if (isset($draw_in_human_time_escaped) && !empty($draw_in_human_time_escaped)) : ?>
                            <time datetime="2597932945" class="widget-ticket-time-remain mobile-hidden">
                                <span class="fa fa-clock-o" aria-hidden="true"></span><?= $draw_in_human_time_escaped ?>
                            </time>
                        <?php endif; ?>
                    </div>
                    <div class="widget-ticket-header-wrapper">
                        <div class="widget-ticket-image widget-ticket-image-raffle">
                            <img src="<?= UrlHelper::esc_url($raffle_image); ?>" alt="<?= htmlspecialchars(_($raffle->name)); ?>">
                        </div>
                        <div class="widget-ticket-header widget-ticket-header-raffle widget-div-title">
                            <h1 class="play-lottery" id="play-lottery">
                                <?php echo get_the_title();?>
                            </h1>
                            <div class="play-lottery-jackpot-amount">
                                <span class="main-prize"><?= _('Main prize') ?>:</span>
                                <span class="currency-front raffle-prize-to-update">
                                    <?php
                                        switch (true) {
                                            case $is_prize_in_tickets($main_prize_tier):
                                                echo $main_prize_tier->tier_prize_in_kind->name;
                                                break;
                                            case $is_prize_in_kind($main_prize_tier):
                                                echo $main_prize_tier->tier_prize_in_kind->name . '(' . Lotto_View::format_currency($main_prize_tier->tier_prize_in_kind->per_user, $raffle->currency->code) . ')';
                                                break;
                                            default:
                                                echo Lotto_View::format_currency($raffle->main_prize, $raffle->currency->code);
                                        }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="widget-ticket-entity widget-raffle-ticket-entity">
                        <div class="widget-raffle-button-wrapper">
                            <div class="widget-raffle-filter-button-wrapper">
                                <button type="button" class="btn-sm widget-raffle-filter-button all-numbers active"><?= _('All') ?> (<span><?= $max_lines_per_draw ?></span>)</button>
                                <button type="button" class="btn-sm widget-raffle-filter-button even-numbers"><?= _('Even') ?> (<span><?= ceil($max_lines_per_draw / 2) ?></span>)</button>
                                <button type="button" class="btn-sm widget-raffle-filter-button odds-numbers"><?= _('Odd') ?> (<span><?= floor($max_lines_per_draw / 2) ?></span>)</button>
                            </div>
                            <div class="widget-raffle-random-button-wrapper">
                                <button type="button" class="btn btn-sm btn-tertiary raffle-ticket-add-random js-enabled-on-free-tickets" disabled="disabled"><?= _("I'm feeling lucky") ?></button>
                                <button type="button" class="btn btn-sm btn-tertiary raffle-ticket-clear-all js-enabled-on-tickets" disabled="disabled"><span class="fa fa-solid fa-trash-can" aria-hidden="true"></span></button>
                            </div>
                        </div>
                        <div class="widget-raffle-checkbox-wrapper">
                            <input id="js-availability-switcher-checkbox" type="checkbox" checked/>
                            <label for="js-availability-switcher-checkbox"><?= _('Show only available ticket numbers') ?></label>
                        </div>
                        <div class="widget-ticket-numbers" style="overflow-x: hidden; min-height: 500px;">
                            <?php for ($number = 1; $number <= $max_lines_per_draw; $number++) : ?>
                                <span class="js-playable raffle-number" data-number="<?= $number ?>"><?= $format_number($number) ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <?php if ($raffle->getFirstRule()->has_prize_in_kind && $raffle_slug === 'lottery-king-raffle'): ?>
                    <?php # this logic should be moved to separated widget, but no time for this?>
                    <div class="widget-ticket-entity2 widget-raffle-ticket-summary">
                        <div class="prize-widget-container" style="background-image: url('/wp-content/plugins/lotto-platform/public/images/raffle/widget/tesla.png')">
                            <div class="pool-label">
                                <div><span class="available-numbers-to-update"></span> / <?= Lotto_View::format_number($raffle->getFirstRule()->max_lines_per_draw) . ' ' . _('tickets') ?></div>
                            </div>

                                    <div class="prize-widget-button">
                                        <a href="#prize"
                                           class="btn btn-primary widget-small-lottery-button"><?= _('Read more about the prize') ?></a>
                                    </div>
                                </div>

                                <div class="pool-article">
                                    <h2 name="prize">Tesla</h2>
                                    <p>
                                        Playing is about winning and Lottery King Raffle makes it even more exciting by
                                        introducing a brand new Tesla 3 as the main prize! With nearly every seventh
                                        ticket winning, the chances for a prize are as high as never before. One of the
                                        winners receives a truly royal prize.
                                    </p>
                                    <p>
                                        Tesla has become a synonym for the electric revolution among drivers and hasn’t
                                        become one of the most valuable companies in the world for no reason. Tesla 3 is
                                        a five-door compact sedan with an eye-catching design. This fully electric car
                                        has a reach of over 400km on a single charge and goes 0-60mph in less than 6
                                        seconds thanks to its nearly 300hp engine.
                                    </p>
                                    <p>
                                        Tesla has been working on Model 3 (initially called BlueStar) for nearly 10
                                        years and the effect is more than satisfying. This highly-awaited car has been
                                        presented on March 31 2016 and has become an instant hit with 325.000 orders
                                        just a week later. Great looking, eco-friendly, and fast. Can you ask for more?
                                        With a bit of luck, a brand new Tesla 3 is just a few clicks away. Pick your
                                        lucky ticket and keep your fingers crossed for becoming the owner of this
                                        amazing prize!
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                    <div class="widget-ticket-entity widget-raffle-ticket-summary">
                        <?= _('Available tickets'); ?>: <strong class="available-numbers-to-update"></strong><br />
                        <?= _('Single ticket price'); ?>: <strong data-price="<?= $line_price ?>" class="raffle-line-price">
                            <span class="loading"></span>
                        </strong><br />

                        <?= _('Your tickets') ?> <span class="raffle-chosen-number-count bold">(<span style="font-size: 1rem; font-weight: bold;">0</span>)</span>:<br />

                        <?php // selected tickets widget?>
                        <div class="widget-chosen-tickets-container"></div>

                        <div class="widget-raffle-total-price">
                            <?= _('Summary'); ?>: <strong class="raffle-total-value">
                                <span class="loading"></span>
                            </strong>
                        </div>
                        <button class="btn btn-primary widget-ticket-summary-button raffle-order-ticket" disabled="disabled">
                            <?= _("Buy tickets"); ?>
                        </button>
                        <?php if ($raffle->whitelabel_raffle->is_bonus_balance_in_use): ?>
                            <p class="note bonus-note"><strong><?= _('NOTE:'); ?></strong> <?= _('Tickets will be paid with your bonus balance.'); ?></p>
                        <?php else: ?>
                            <p class="note"><strong><?= _('NOTE:'); ?></strong> <?= _('Payments for raffle tickets are possible only using your account balance.'); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="clearfix"></div>
                    <a href="https://access.gaminglabs.com/certificate/index?i=314" class="gli-certificate-box row mx-0" rel="nofollow" target="_blank">
                        <div class="gli-certificate-img-wrapper">
                            <img src="<?= get_template_directory_uri() . '/images/widgets/gli/gli-check.png' ?>" alt="Gaming Labs Certified">
                        </div>
                        <div class="col gli-certificate-text-wrapper">
                            <p class="text-primary">
                                <strong><?= _("Draw certified by Gaming Laboratories International") ?></strong>
                            </p>

                            <p><small><?= _("Our True Random Number Generator has been certified by Gaming Laboratories International to ensure the highest security and guarantee 100% fairness of the drawing process.") ?></small></p>
                        </div>
                        <div class="gli-certificate-img-wrapper">
                            <img src="<?= get_template_directory_uri() . '/images/widgets/gli/gli.jpg' ?>" alt="Gaming Labs Certified">
                        </div>
                    </a>
                    <h2><a href="https://<?= $whitelabel['domain'] ?>/how-does-trng-work/" target="_blank">Click here to see how does the TRNG work</a></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="main-width content-width">
        <div class="content-box play-box">
            <section class="page-content page-content-more">
                <article class="page">
                    <?php
                        the_content();
                    ?>
                </article>
            </section>
        </div>
    </div>
</div>
