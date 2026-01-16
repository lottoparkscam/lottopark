<?php

use Helpers\UrlHelper;

?>
<link rel="stylesheet" type="text/css" href="<?= $css_path; ?>"/>

<div class="widget-7">
    <div class="widget-top-title">
        <img src="<?= $whitelabel_image_path; ?>"/>
    </div>
    <div class="widget-content" id="widget-content-<?=$widget_div_id;?>">
        <div class="widget-ball">
            <img src="<?= $lotteries[0]['ball']; ?>"/>
        </div>

        <div class="widget-lottery-name">
            <?= $lotteries[0]['lotteryName']; ?>
        </div>

        <div class="widget-jackpot">
            <?= $lotteries[0]['price']; ?>
        </div>

        <div class="widget-bottom">
            <div class="widget-time-left-title"><?= _("next jackpot"); ?></div>
            <div class="widget-time-left"><span
                        class="calendar-icon"></span> <?=Lotto_View::get_formatted_date_for_widget($lotteries[0]['lottery']);?>
            </div>

            <a href="<?=UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play/' . $lotteries[0]['lottery']['slug'])); ?><?= $query_string; ?>" class="widget-play-now"><?=_("Play now"); ?></a>
        </div>
    </div>
</div>
