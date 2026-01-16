<?php

use Helpers\UrlHelper;

$isAspectRatioSet = !empty($width) && !empty($height);

$classes = [];
$classes[] = $isAspectRatioSet ? 'aspect-ratio-set' : null;
$classes[] = $visibility === 'all' ? 'banner-visible-for-all': null;
$classes[] = $visibility === 'guests' ? 'only-not-logged-user': null;
$classes[] = $visibility === 'users' ? 'only-logged-user': null;
$classes[] = Lotto_Settings::getInstance()->get('widget_cnt') == 1 ? 'widget-banner-mobile-nmt' : null;

?>
<div class="widget-lotto-banner <?= implode(' ', array_filter($classes)); ?>">
    <div class="main-width">
        <div class="widget-lotto-banner-wrapper" <?php echo ($isAspectRatioSet) ? 'style="position:relative;aspect-ratio: ' . $width . ' / ' . str_replace('px', '', $height) . '"': null;?>>
            <?php 
                echo (!empty($link_to)) 
                    ? '<a' 
                        . (!empty($args['widget_id']) ? " id=\"{$args['widget_id']}\"" : '')
                        . ' href="' . UrlHelper::esc_url(strtolower($link_to)) . '"' 
                        . " target=\"{$target}\">"
                    : null;
            ?>
            <img class="lp-img" src="<?= $image; ?>" style="<?php echo ($isAspectRatioSet) ? 'position:absolute;top:0;left:0;': null;?>height: <?= $height; ?>" alt="<?php echo (!empty($altText)) ? $altText : ''; ?>">
            <?php echo (!empty($link_to)) ? '</a>': null; ?>
        </div>
    </div>
</div>
