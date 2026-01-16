<?php

if (!defined('WPINC')) {
    die;
}

/** @var Lotto_Widget_Raffle_Settings $setting */
$setting;

$background_image = 'https://fatelotto.com/wp-content/uploads/sites/47/slider_bg.png';
$raffle_image = 'https://fatelotto.com/wp-content/uploads/sites/47/raffle-all-logo.png';
?>
<div class="promo-widget-container promo-widget-container-fatelotto-raffle">
    <div class="promo-widget__overflow"></div>

    <div id="<?= $widgetId ?>" class="promo-widget<?php if ($setting->is_small()): ?> promo-widget--small<?php endif; ?>" <?php if ($background_image): ?>style="background: url(<?= $background_image ?>)"<?php endif; ?>>

        <div class="promo-widget__logo">
            <img src="<?= $raffle_image ?>" alt="Fatelotto Raffle Logo"/>
        </div>

        <div class="promo-widget__prize">
            <div class="promo-widget__prize-item promo-widget__prize-item--featured">
                <div class="promo-widget__prize-item-text"><?= _('Win up to €100.000,- with a €50,- Raffle ticket!') ?></div>
            </div>
            <div class="promo-widget__prize-item">
                <span class="raffle-name"><?= $raffle->name ?></span>
            </div>
        </div>

        <div class="promo-widget__button">
            <a href="<?php echo lotto_platform_get_permalink_by_slug('raffle'); ?>" class="btn btn-default btn-xl js-play"><?= _("Play now") ?></a>
        </div>

    </div>
</div>
<?php include(__DIR__ . '/../widgetStyle.php'); ?>
