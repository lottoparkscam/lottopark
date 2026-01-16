<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

?>
<div class="small-widget small-widget-draw">
    <?php
        if (!empty($lottery)):
            $lotterySlug = $lottery['slug'];
            $lottery_image = Lotto_View::get_lottery_image($lottery['id']);
            $play_info_href = lotto_platform_get_permalink_by_slug('play/' . $lotterySlug);
    ?>
            <div class="small-widget-draw-image">
                <img src="<?= UrlHelper::esc_url($lottery_image); ?>" 
                     alt="<?= htmlspecialchars(_($lottery['name'])); ?>">
            </div>
    
            <h2 class="small-widget-draw-title">
                <a href="<?= UrlHelper::esc_url($play_info_href); ?>">
                    <?= empty($title) ? _($lottery['name']) : $title; ?>
                </a>
            </h2>
    
            <div class="small-widget-draw-amount jackpot-to-update-<?= $lotterySlug ?>">
                <span class="loading"></span>
                <!--  This part will be generated automatically by JS-->
            </div>
            
            <time class="widget-ticket-time-remain next-real-draw-short-to-update-<?= $lotterySlug ?>">
                <span class="fa fa-clock-o" aria-hidden="true"></span>
                <!--  This part will be generated automatically by JS-->
            </time>
            <div class="widget-small-draw-button-container">
                <a href="<?= UrlHelper::esc_url($play_info_href); ?>"
                class="btn btn-primary widget-small-lottery-button play-button" data-lottery-slug="<?= $lotterySlug ?>">
                    <?= _('Play now') ?>
                </a>
            </div>

    <?php
        else:
    ?>
            <div class="small-widget-no-info">
                <?= _("This lottery is inactive.") ?>
            </div>
    <?php
        endif;
    ?>
</div>
