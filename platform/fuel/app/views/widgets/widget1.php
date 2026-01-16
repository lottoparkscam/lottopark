<?php

use Helpers\UrlHelper;

?>
<link rel="stylesheet" type="text/css" href="<?= $css_path; ?>"/>

<div class="widget-1">
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

        <div class="widget-lottery-time">
            <span class="calendar-icon"></span> <?php echo Security::htmlentities(Lotto_View::format_date_without_timezone($lotteries[0]['lottery']['last_date_local'], IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, 'd MMMM, hh:mm')); ?>
        </div>

        <div class="draw-results">
            <?php foreach ($lotteries[0]['last_numbers'] as $key => $number): ?>
                <div class="ball white-ball"><div class="white-ball-shadow"><?= $number; ?></div></div>
            <?php endforeach; ?>

            <?php foreach ($lotteries[0]['last_bnumbers'] as $key => $number): ?>
                <?php if (!empty($number)): ?>
                    <div class="ball bonus-ball"><div class="color-ball-shadow"><?= $number; ?></div></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="widget-bottom">
        <div class="widget-nearest-jackpot"><?php echo _("next jackpot"); ?></div>
        <div class="widget-jackpot"><?= $lotteries[0]['price']; ?></div>

        <a href="<?php echo UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play/' . $lotteries[0]['lottery']['slug'])); ?><?= $query_string; ?>" class="widget-play-now"><?php echo _("Play now"); ?></a>
    </div>
</div>
