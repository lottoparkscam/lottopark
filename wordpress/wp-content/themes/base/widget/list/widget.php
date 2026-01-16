<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

$showitems = 3;
if ((int)$display === Lotto_Widget_List::DISPLAY_TALL) {
    $showitems = 5;
}

$widget_list_classes = ' widget-list-type';
if ((int)$count_type === Lotto_Widget_List::COUNTDOWN_24HOURS) {
    $widget_list_classes .= '2';
} else {
    $widget_list_classes .= '1';
}
$widget_list_classes .= ' widget-list-display';
if ((int)$display === Lotto_Widget_List::DISPLAY_SHORT) {
    $widget_list_classes .= '2';
} else {
    $widget_list_classes .= '1';
}

?>
<div class="main-width">
    <div class="widget-list-content <?= $widget_list_classes; ?>">
        <?php
            if (!empty($title)):
        ?>
                <div class="widget-list-title"><?= $title; ?></div>
        <?php
            endif;
            
            if (!empty($lotteries) && count($lotteries) > 0):
                if (count($lotteries) > $showitems):
        ?>
                    <div class="widget-list-carousel-prev">
                        <a href="#" aria-label="<?= _('Previous') ?>">
                            <span class="fa fa-angle-left" aria-hidden="true"></span>
                        </a>
                    </div>
            <?php
                endif;
            ?>
                <div class="widget-list-carousel" data-showitems="<?= $showitems; ?>">
                    <ul><?php
                        $lottery_number = 0;
                        foreach ($lotteries as $lottery):
                            $lotterySlug = $lottery['slug'];
                            $lottery_number++;
                            
                            if (isset($count) && $lottery_number > $count):
                                break;
                            endif;
                            
                            $lottery_image = Lotto_View::get_lottery_image($lottery['id']);

                            $lottery_image_path = Lotto_View::get_lottery_image_path($lottery['id']);

                            $image_size = null;
                            if (!empty($lottery_image_path)) {
                                $image_size_check = getimagesize($lottery_image_path);
                                if ($image_size_check !== false) {
                                    $image_size = $image_size_check;
                                }
                            }

                            if (!$lottery['playable']) {
                                continue;
                            }
                            
                            ?><li class="widget-list-ticket" data-lottery-slug="<?= $lottery['slug'] ?>">
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
                                if ((int)$count_type === Lotto_Widget_List::COUNTDOWN_ALWAYS):
                            ?>
                                        <time class="widget-list-ticket-countdown simple-countdown next-real-draw-timestamp-to-update-<?= $lotterySlug ?>"
                                              data-lottery-slug="<?= $lotterySlug ?>"
                                              data-count-type="<?= (int)$count_type ?>"
                                        >
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
                                                    <?= _("featured" . "\004" . "hrs") ?>
                                                </span>
                                            </span>
                                            <span class="widget-list-countdown-group">
                                                <span class="widget-list-countdown-item countdown-item">
                                                    <span class="loading"></span>
                                                </span><br>
                                                <span class="widget-list-countdown-label">
                                                    <?= _("featured" . "\004" . "min") ?>
                                                </span>
                                            </span>
                                            <span class="widget-list-countdown-group">
                                                <span class="widget-list-countdown-item countdown-item">
                                                    <span class="loading"></span>
                                                </span><br>
                                                <span class="widget-list-countdown-label">
                                                    <?= _("featured" . "\004" . "sec") ?>
                                                </span>
                                            </span>
                                        </time>
                                <?php
                                    elseif ((int)$count_type === Lotto_Widget_List::COUNTDOWN_24HOURS):
                                ?>
                                        <time class="widget-list-ticket-countdown simple-countdown next-real-draw-timestamp-to-update-<?= $lotterySlug ?>"
                                              data-lottery-slug="<?= $lotterySlug ?>"
                                              data-count-type="<?= (int)$count_type ?>"
                                        >
                                            <span class="widget-list-ticket-countdown-before widget-list-countdown-to-update-<?= $lotterySlug ?>">
                                                <span class="fa fa-clock-o" aria-hidden="true"></span>
                                            </span>
                                        </time>
                            <?php
                                    endif;
                            ?>
                                <div class="widget-list-button-container <?= $showitems === 3 ? 'pl-0' : '' ?>">
                                    <button class="btn btn-primary widget-list-button">
                                        <?= _("Play now") ?>
                                    </button>
                                </div>
                            </li><?php

                        endforeach;
                    ?></ul>
                    <div class="clearfix"></div>
                    <div class="mobile-only">
                        <a class="btn btn-secondary" href="<?php
                            echo UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play')); ?>">
                                <?= _("See more") ?>
                        </a>
                    </div>
                </div>
                <div class="clearfix"></div>
        <?php
                if ($count > $showitems):
        ?>
                    <div class="widget-list-carousel-next">
                        <a href="#" aria-label="<?= _('Next') ?>">
                            <span class="fa fa-angle-right" aria-hidden="true"></span>
                        </a>
                    </div>
        <?php
                endif;
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
