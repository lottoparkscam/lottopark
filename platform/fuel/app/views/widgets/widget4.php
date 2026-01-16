<?php

use Helpers\UrlHelper;

?>
<link rel="stylesheet" type="text/css" href="<?= $css_path; ?>"/>

<div class="widget-4">
    <div class="widget-top-title">
        <img src="<?= $whitelabel_image_path; ?>"/>
    </div>
    <div class="widget-content" id="widget-content-<?=$widget_div_id;?>">
        <?php foreach ($lotteries as $slug => $lottery): ?>
            <div class="widget-lottery lottery-<?= $slug; ?>">
                <div class="widget-left">
                    <div class="widget-ball">
                        <img src="<?= $lottery['ball']; ?>"/>
                    </div>
                </div>

                <div class="widget-right">
                    <div class="widget-lottery-name">
                        <?= $lottery['lotteryName']; ?>
                    </div>

                    <div class="widget-lottery-time">
                        <span class="calendar-icon"></span> <?php echo Security::htmlentities(Lotto_View::format_date_without_timezone($lottery['lottery']['last_date_local'], IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, 'd MMMM, hh:mm')); ?>
                    </div>
                </div>

                <div class="widget-bottom">
                    <div class="draw-results">
                        <?php foreach ($lottery['last_numbers'] as $key => $number): ?>
                            <?php if (!empty($number)): ?>
                                <div class="ball white-ball"><div class="white-ball-shadow"><?= $number; ?></div></div>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php foreach ($lottery['last_bnumbers'] as $key => $number): ?>
                            <?php if (!empty($number)): ?>
                                <div class="ball bonus-ball"><div class="color-ball-shadow"><?= $number; ?></div></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <a href="<?php echo UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play/' . $lottery['lottery']['slug'])); ?><?= $query_string; ?>" class="widget-play-now"><?php echo _("Play now"); ?></a>

                    <div class="widget-nearest-jackpot"><?php echo _("next jackpot"); ?></div>
                    <div class="widget-jackpot"><?= $lottery['price']; ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
