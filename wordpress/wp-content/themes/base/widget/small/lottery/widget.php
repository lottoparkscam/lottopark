<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

$lottery_image = Lotto_View::get_lottery_image($lottery['id']);

$play_info_href = lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']);

?>
<div class="small-widget small-widget-lottery <?php if (!empty($lottery)): echo ' small-widget-lottery-'.$lottery['slug']; endif; ?>">
    <?php
        if (!empty($lottery)):
    ?>
            <h2 class="small-widget-title"><a href="<?php
                    echo UrlHelper::esc_url($play_info_href);
                ?>"><?php
                    echo empty($title) ? _($lottery['name']) : $title;
                ?></a></h2>
            <div class="small-widget-content small-widget-lottery-content">
                <div class="small-widget-lottery-image">
                    <img src="<?= UrlHelper::esc_url($lottery_image); ?>" 
                         alt="<?= htmlspecialchars(_($lottery['name'])); ?>">
                </div>
                <p><?= $content; ?></p>
                <div class="widget-small-lottery-button-container">
                    <a href="<?= UrlHelper::esc_url($play_info_href); ?>" 
                       class="btn btn-default widget-small-lottery-button play-button"
                       data-lottery-slug="<?= $lottery['slug'] ?>"
                    >
                        <?= _('Play now') ?>
                    </a>
                </div>
                <div class="clearfix"></div>
            </div>
    <?php
        else:
    ?>
            <div class="small-widget-no-info"><?= _("This lottery is inactive.") ?></div>
    <?php
        endif;
    ?>
</div>
