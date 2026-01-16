<?php

use Helpers\UrlHelper;
use Helpers\Wordpress\LanguageHelper;

if (!defined('WPINC')) {
    die;
}

$settingsLanguageKey = str_replace('_', '', get_locale() ?? 'en_US');

// slider is a special element
// because his margin/padding may differ in different situations (THANK YOU GRAPHIC DESIGNERS)
// small mobile: no padding (top or bottom depending on the placing)
// large desktop & mobile: no margin (top or bottom depending on the placing)
// WARN: this not applies to all areas, only to home area, bottom areas and some top areas (pages with "breadcrumbs" like lottery info, results, play, news)

$classes = array();
// For now we want same view like type no. 1
if ((int)$type === Lotto_Widget_Featured::TYPE_WITH_BACKGROUND) {
    $type = Lotto_Widget_Featured::TYPE_SMALL;
    $saved_type = 'large';
} else {
    $saved_type = 'small';
}

$sidebars_array = array(
    'frontpage-sidebar-id',
    'info-more-sidebar-id',
    'lottery-info-more-sidebar-id',
    'results-more-sidebar-id',
    'lottery-results-more-sidebar-id',
    'play-more-sidebar-id',
    'play-lottery-more-sidebar-id'
);

if ((int)$type === Lotto_Widget_Featured::TYPE_SMALL) { // small
    if (Lotto_Settings::getInstance()->get("widget_cnt") == 1) {
        // widget is first!
        if (!empty($args['id']) && in_array($args['id'], $sidebars_array)) {
            $classes[] = 'widget-featured-small-mobile-nmt';
        }
    } // can be both: first & last
    if (Lotto_Settings::getInstance()->get("widget_total_cnt") == Lotto_Settings::getInstance()->get("widget_cnt")) {
        // widget is last!
        $classes[] = 'widget-featured-small-mobile-nmb';
    }
} elseif ((int)$type === Lotto_Widget_Featured::TYPE_LARGE) { // large
    if (Lotto_Settings::getInstance()->get("widget_cnt") == 1) {
        if (in_array($args['id'], $sidebars_array)) {
            $classes[] = 'widget-featured-large-nmt';
        }
    }
    if (Lotto_Settings::getInstance()->get("widget_total_cnt") == Lotto_Settings::getInstance()->get("widget_cnt")) {
        $classes[] = 'widget-featured-large-nmb';
    }
}

$widget_additional_class = ' widget-featured-wrapper-large';
if ((int)$type === Lotto_Widget_Featured::TYPE_SMALL) {
    $widget_additional_class = ' widget-featured-wrapper-small';
}

if (!empty($classes)) {
    $widget_additional_class .= ' ' . implode(" ", $classes);
}

$currentLanguage = LanguageHelper::getCurrentWhitelabelLanguage();
$currentLanguage = str_replace('_', '', $currentLanguage['code']);

?>
<div class="widget-featured-wrapper <?= $widget_additional_class ?> widget-featured-bg-type-<?= $saved_type; ?> <?= $settings['bg_large_disable_mobile'] ? 'widget-featured-large-bg-no-bg-mobile' : '' ?>">
    <?php
    if ($saved_type == 'large') :
    ?>
        <div class="main-width<?php echo ($settings['bg_large_disable_mobile'] == 1) ? ' widget-featured-large-bg-disable-mobile' : '';?>">
            <div class="widget-featured-large-bg-text-content">
                <div class="widget-featured-large-bg-text widget-featured-large-bg-text-title"><?php echo (!empty($settings[$currentLanguage]['title'])) ? $settings[$currentLanguage]['title'] : $instance['bg_large_text'];?></div>
                <div class="widget-featured-large-bg-text widget-featured-large-bg-text-subtitle"><?php echo (!empty($settings[$currentLanguage]['subtitle'])) ? $settings[$currentLanguage]['subtitle'] : $instance['bg_large_subtext'];?></div>
            </div>
        </div>
    <?php
    endif;
    ?>

    <div class="main-width">
        <div class="widget-featured-content" data-active="0">
            <?php
            if (!empty($lotteries) && count($lotteries) > 0) :
                if ($slidecount > 1) :
            ?>
                    <div class="widget-featured-pager">
                        <?php for ($i = 0; $i < count($lotteries) && $i < $slidecount; $i++) : ?>
                            <a href="#" data-index="<?= $i; ?>"<?= $i == 0 ? ' class="widget-featured-page-active"' : '' ?>>
                                <span class="fa fa-circle-o" aria-hidden="true"></span>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php
                endif;

                for ($i = 0; $i < count($lotteries) && $i < $slidecount; $i++) :
                    $lottery_id = $lotteries[$i]['id'];
                    $lottery_name = "";
                    $lotterySlug = $lotteries[$i]['slug'];
                    if (
                        !empty($settings[$settingsLanguageKey]) &&
                        !empty($settings[$settingsLanguageKey]['lotteries']) &&
                        !empty($settings[$settingsLanguageKey]['lotteries'][$lottery_id]) &&
                        !empty($settings[$settingsLanguageKey]['lotteries'][$lottery_id]['name'])
                    ) {
                        $lottery_name = $settings[$settingsLanguageKey]['lotteries'][$lottery_id]['name'];
                    } elseif (_($lotteries[$i]['name'])) {
                        $lottery_name = _($lotteries[$i]['name']);
                    }

                ?>
                    <div class="widget-featured-item<?= $i != 0 ? ' hidden-normal': '' ?>">
                        <div class="widget-featured-flex">
                        <?php
                        switch ((int)$type):
                            case Lotto_Widget_Featured::TYPE_LARGE:
                        ?>
                                <h2><?= Security::htmlentities($lottery_name); ?></h2>
                                <br>
                                <div class="hamount jackpot-to-update-<?php echo ($lotteries[$i]['type'] === 'raffle') ? 'raffle-' . $lotterySlug : $lotterySlug;?>">
                                    <!-- this part will be generated automatically by JS-->
                                </div>
                                <br>
                                <?php if ($lotteries[$i]['type'] !== 'raffle'):?>
                                    <time class="platform-countdown next-real-draw-timestamp-to-update-<?= $lotterySlug ?>">
                                        <span class="digit-group">
                                            <span class="digit-anim">
                                                <span class="digit-next-up"></span>
                                                <span class="digit-next-bottom"></span>
                                                <span class="digit">
                                                <span class="loading"></span>
                                                </span>
                                                <span class="digit-bottom">
                                                    <span class="loading"></span>
                                                </span>
                                            </span>
                                            <span class="digit-anim">
                                                <span class="digit-next-up"></span>
                                                <span class="digit-next-bottom"></span>
                                                <span class="digit">
                                                    <span class="loading"></span>
                                                </span>
                                                <span class="digit-bottom">
                                                    <span class="loading"></span>
                                                </span>
                                            </span>
                                            <span class="digit-group-label">
                                                <?= _("days") ?>
                                            </span>
                                        </span>
                                        <span class="digit-group">
                                            <span class="digit-anim">
                                                <span class="digit-next-up"></span>
                                                <span class="digit-next-bottom"></span>
                                                <span class="digit">
                                                    <span class="loading"></span>
                                                </span>
                                                <span class="digit-bottom">
                                                    <span class="loading"></span>
                                                </span>
                                            </span>
                                            <span class="digit-anim">
                                                <span class="digit-next-up"></span>
                                                <span class="digit-next-bottom"></span>
                                                <span class="digit">
                                                    <span class="loading"></span>
                                                </span>
                                                <span class="digit-bottom">
                                                    <span class="loading"></span>
                                                </span>
                                            </span>
                                            <span class="digit-group-label">
                                                <?= _("featured" . "\004" . "hrs") ?>
                                            </span>
                                        </span>
                                        <span class="digit-group">
                                            <span class="digit-anim">
                                                <span class="digit-next-up"></span>
                                                <span class="digit-next-bottom"></span>
                                                <span class="digit">
                                                    <span class="loading"></span>
                                                </span>
                                                <span class="digit-bottom">
                                                    <span class="loading"></span>
                                                </span>
                                            </span>
                                            <span class="digit-anim">
                                                <span class="digit-next-up"></span>
                                                <span class="digit-next-bottom"></span>
                                                <span class="digit">
                                                    <span class="loading"></span>
                                                </span>
                                                <span class="digit-bottom">
                                                    <span class="loading"></span>
                                                </span>
                                            </span>
                                            <span class="digit-group-label">
                                                <?= _("featured" . "\004" . "min") ?>
                                            </span>
                                        </span>
                                        <span class="digit-group">
                                            <span class="digit-anim">
                                                <span class="digit-next-up"></span>
                                                <span class="digit-next-bottom"></span>
                                                <span class="digit">
                                                    <span class="loading"></span>
                                                </span>
                                                <span class="digit-bottom">
                                                    <span class="loading"></span>
                                                </span>
                                            </span>
                                            <span class="digit-anim">
                                                <span class="digit-next-up"></span>
                                                <span class="digit-next-bottom"></span>
                                                <span class="digit">
                                                    <span class="loading"></span>
                                                </span>
                                                <span class="digit-bottom">
                                                    <span class="loading"></span>
                                                </span>
                                            </span>
                                            <span class="digit-group-label">
                                                <?= _("featured" . "\004" . "sec") ?>
                                            </span>
                                        </span>
                                    </time>
                                <?php endif;?>
                                <?php
                                $pagePlaySlug = $lotteries[$i]['type'] === 'raffle' ? 'play-raffle' : 'play';
                                ?>
                                <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug($pagePlaySlug . '/' . $lotteries[$i]['slug'])); ?>"
                                   class="btn btn-secondary btn-xl widget-featured-button"
                                >
                                    <?= _("Get a Ticket") ?>
                                </a>
                            <?php
                                break;
                            case Lotto_Widget_Featured::TYPE_SMALL:
                                if ($lotteries[$i]['type'] === 'raffle') {
                                    $lottery_image = Lotto_View::get_lottery_image($lotteries[$i]['id'], null, 'raffle');
                                    $lottery_image_path = Lotto_View::get_lottery_image_path($lotteries[$i]['id'], null, 'raffle');
                                } else {
                                    $lottery_image = Lotto_View::get_lottery_image($lotteries[$i]['id']);
                                    $lottery_image_path = Lotto_View::get_lottery_image_path($lotteries[$i]['id']);
                                }

                                $image_size = null;
                                if (!empty($lottery_image_path)) {
                                    $image_size_check = getimagesize($lottery_image_path);
                                    if ($image_size_check !== false) {
                                        $image_size = $image_size_check;
                                    }
                                }
                            ?>
                                    <div class="widget-featured-image">
                                        <?php if (!empty($image_size)):?>
                                            <div style="background-image: url(<?= UrlHelper::esc_url($lottery_image); ?>)" aria-label="<?= htmlspecialchars(_($lotteries[$i]['name'])); ?>"></div>
                                        <?php
                                        endif;
                                        ?>
                                    </div>
                                <div class="widget-featured-info">
                                    <h2><?= $lottery_name ?></h2>
                                    <div class="widget-featured-info-container">
                                        <div class="widget-featured-amount jackpot-to-update-<?php echo ($lotteries[$i]['type'] === 'raffle') ? 'raffle-' . $lotterySlug : $lotterySlug;?>">
                                            <!-- this part will be generated automatically by JS-->
                                        </div>
                                    </div>
                                </div>
                                <?php if ($lotteries[$i]['type'] !== 'raffle'):?>
                                    <div class="widget-featured-countdown">
                                        <time class="widget-list-ticket-countdown simple-countdown next-real-draw-timestamp-to-update-<?= $lotterySlug ?>" data-lottery-slug="<?= $lotterySlug ?>">
                                            <span class="widget-list-countdown-group">
                                                <span class="widget-list-countdown-item countdown-item">
                                                    <span class="loading"></span>
                                                </span><br>
                                                <span class="widget-list-countdown-label">
                                                    <?= _("days") ?>
                                                </span>
                                            </span>
                                            <span class="widget-list-countdown-group">
                                                <span class="widget-list-countdown-item countdown-item">
                                                    <span class="loading"></span>
                                                </span><br>
                                                <span class="widget-list-countdown-label">
                                                    <?= str_replace("featured" . "\004", '', _("featured" . "\004" . "hrs")) ?>
                                                </span>
                                            </span>
                                            <span class="widget-list-countdown-group">
                                                <span class="widget-list-countdown-item countdown-item">
                                                    <span class="loading"></span>
                                                </span><br>
                                                <span class="widget-list-countdown-label">
                                                    <?= str_replace("featured" . "\004", '', _("featured" . "\004" . "min")) ?>
                                                </span>
                                            </span>
                                            <span class="widget-list-countdown-group">
                                                <span class="widget-list-countdown-item countdown-item">
                                                    <span class="loading"></span>
                                                </span><br>
                                                <span class="widget-list-countdown-label">
                                                    <?= str_replace("featured" . "\004", '', _("featured" . "\004" . "sec")) ?>
                                                </span>
                                            </span>
                                        </time>
                                    </div>
                                <?php endif;?>
                                <?php
                                $pagePlaySlug = $lotteries[$i]['type'] === 'raffle' ? 'play-raffle' : 'play';
                                ?>
                                <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug($pagePlaySlug . '/' . $lotteries[$i]['slug'])); ?>" class="btn btn-default btn-xl widget-featured-button"><?= _("Play now") ?></a>
                                <div class="clearfix"></div>
                            <?php
                                    break;
                            endswitch;
                            ?>
                        </div>
                    </div>
                <?php
                endfor;
            else :
                ?>
                <div class="widget-featured-nolottery">
                    <?= _('No active lotteries.') ?>
                </div>
            <?php
            endif;
            ?>
        </div>
    </div>
</div>

<?php
if ($settings['useCustomColors'] !== false): ?>
<style>
    #<?= $widgetId ?> .widget-featured-wrapper-small .widget-featured-content {
        background-color: <?= $settings['backgroundColor'] ?>!important;
    }
    #<?= $widgetId ?> .widget-featured-button {
        color: <?= $settings['buttonTextColor'] ?>;
        background-color: <?= $settings['buttonBackgroundColor']; ?>!important;
    }
    #<?= $widgetId ?> .widget-featured-button:hover {
        color: <?= $settings['buttonTextColorOnHover']; ?>!important;
        background-color: <?= $settings['buttonBackgroundColorOnHover']; ?>!important;
    }
</style>
<?php endif; ?>