<?php

use Carbon\Carbon;
use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

$widget_list_classes = ' widget-list-type';
if ((int)$count_type === Lotto_Widget_List::COUNTDOWN_24HOURS) {
    $widget_list_classes .= '2';
} else {
    $widget_list_classes .= '1';
}
$showitems = 3;
$widget_list_classes .= ' widget-list-display';
if ((int)$display === Lotto_Widget_List::DISPLAY_SHORT) {
    $widget_list_classes .= '2';
} else {
    $widget_list_classes .= '1';
    $showitems = 5;
}

?>
<div class="main-width">
    <div class="widget-list-content-grid <?= $widget_list_classes; ?>">
        <?php
            if (!empty($title)):
        ?>
                <div class="widget-list-title">
                    <?= $title; ?>
                </div>
        <?php
            endif;

            if ($whitelabelHasCasinoBanner):
                if (!in_array(basename(get_permalink()), [
                    basename(UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("play"))),
                    basename(UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("keno"))),
                    basename(UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("welcome"))),
                ])):
        ?>
        <h2 class="text-center">
            <?= __('While you wait for the lottery draw results:') ?>
        </h2>
        <?php
                endif;
        ?>
        <?php if (basename(get_permalink()) !== basename(UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("keno")))):?>
            <div id="casinoPromoBanner">
                <?php $casinoHomepageUrl = UrlHelper::changeAbsoluteUrlToCasinoUrl(lotto_platform_get_permalink_by_slug('/'), true); ?>
                <a href="<?= $casinoHomepageUrl ?>">
                    <img src="<?= UrlHelper::esc_url(get_template_directory_uri().'/images/banners/casinoBanner.png'); ?>">
                </a>
            </div>
        <?php endif;?>
        <?php
            endif;
            if (!empty($lotteries) && count($lotteries) > 0):
        ?>

                <?php get_template_part('partials/search-lottery');?>

                <div class="widget-list-grid">
                    <ul><?php
                        $lottery_number = 0;
                        foreach ($lotteries as $lottery):
                            $lotterySlug = $lottery['slug'];
                            $lottery_number++;

                            if (isset($count) && $lottery_number > $count) {
                                break;
                            }

                            $lottery_image = Lotto_View::get_lottery_image($lottery['id']);
                            
                            $lottery_image_path = Lotto_View::get_lottery_image_path($lottery['id']);
                            
                            $image_size = null;
                            if (!empty($lottery_image_path)) {
                                $image_size_check = getimagesize($lottery_image_path);
                                if ($image_size_check !== false) {
                                    $image_size = $image_size_check;
                                }
                            }
                            
                            $ticket_last_class = '';
                            if ($lottery_number == $count) {
                                $ticket_last_class = ' widget-list-ticket-last';
                            }

                            if (!$lottery['playable']) {
                                continue;
                            }

                        ?><li class="widget-list-ticket <?= $ticket_last_class; ?>" data-lottery-slug="<?= $lottery['slug'] ?>" data-lottery-type="<?php echo (!empty($lottery['type'])) ? $lottery['type'] : ''; ?>">
                            <?php
                                if ((int)$display === Lotto_Widget_List::DISPLAY_TALL):
                            ?>
                                    <?= $title_start_tag ?><a class="widget-list-link"
                                    href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play/' . $lottery['slug'])); ?>">
                                        <?= Security::htmlentities(_($lottery['name'])); ?>
                                    </a><?= $title_end_tag ?>
                                    <div class="widget-list-ticket-image-amount-container">
                                        <div class="widget-list-ticket-image">
                                            <?php
                                                if (!empty($image_size)):
                                            ?>
                                                    <img width="<?= $image_size[0]; ?>" 
                                                         height="<?= $image_size[1]; ?>" 
                                                         src="<?= UrlHelper::esc_url($lottery_image); ?>" 
                                                         alt="<?= htmlspecialchars(_($lottery['name'])); ?>">
                                            <?php
                                                endif;
                                            ?>
                                        </div>
                                        <div class="widget-list-hamount jackpot-to-update-<?= $lotterySlug ?>">
                                            <span class="loading"></span>
                                            <!-- This part will be generated automatically by JS -->
                                        </div>
                                    </div>
                            <?php
                                else:
                            ?>
                                    <div class="widget-list-ticket-image">
                                        <?php
                                            if (!empty($image_size)):
                                        ?>
                                                <img width="<?= $image_size[0]; ?>" 
                                                     height="<?= $image_size[1]; ?>" 
                                                     src="<?= UrlHelper::esc_url($lottery_image); ?>" 
                                                     alt="<?= htmlspecialchars(_($lottery['name'])); ?>">
                                        <?php
                                            endif;
                                        ?>
                                    </div>
                                    <?= $title_start_tag ?><a class="widget-list-link"
                                    href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play/' . $lottery['slug'])); ?>">
                                        <?= Security::htmlentities(_($lottery['name'])); ?>
                                    </a><?= $title_end_tag ?>
                                    <div class="widget-list-hamount jackpot-to-update-<?= $lotterySlug ?>">
                                        <span class="loading"></span>
                                        <!-- This part will be generated automatically by JS -->
                                    </div>
                            <?php
                                endif;
                            ?>
                                <time class="widget-list-ticket-countdown countdown next-real-draw-timestamp-to-update-<?= $lotterySlug ?>"
                                      data-lottery-slug="<?= $lotterySlug ?>"
                                      id="widget-list-ticket-countdown-<?= $lottery['slug'] ?>"
                                >
                                    <div class="widget-list-ticket-countdown-before widget-list-countdown-to-update-<?= $lotterySlug ?>">
                                        <span class="fa fa-clock-o" aria-hidden="true"></span>
                                    </div>
                                </time>
                            <?php
                                $play_slug_t = lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']);
                                $play_slug_text = UrlHelper::esc_url($play_slug_t);
                            ?>
                                <div class="widget-list-button-container <?= $showitems === 3 ? 'pl-0' : '' ?>">
                                    <button href="<?= $play_slug_text; ?>" 
                                       class="btn btn-primary widget-list-button">
                                           <?= _("Play now") ?>
                                    </button>
                                </div>
                            </li><?php
                        endforeach;
                    ?>
                    </ul>
                    <div class="clearfix"></div>
                </div>
        <?php
            else:
        ?>
                <div class="widget-list-nolottery text-center">
                    <?= _('No active lotteries.') ?>
                </div>
        <?php
            endif;
        ?>
    </div>
</div>
