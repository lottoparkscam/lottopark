<?php

use Helpers\UrlHelper;

?>
<link rel="stylesheet" type="text/css" href="<?= $css_path; ?>"/>

<div class="widget-8">
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
                    <a href="<?php echo UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play/' . $lottery['lottery']['slug'])); ?><?= $query_string; ?>" class="widget-play-now"><?=_("Play now");?></a>

                    <div class="widget-lottery-name">
                        <?= $lottery['lotteryName']; ?>
                    </div>

                    <div class="widget-jackpot">
                        <?= $lottery['price']; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
