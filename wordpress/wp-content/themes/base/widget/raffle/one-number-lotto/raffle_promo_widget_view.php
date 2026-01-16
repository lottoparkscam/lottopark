<?php

if (!defined('WPINC')) {
    die;
}

/** @var Lotto_Widget_Raffle_Settings $setting */
$setting;
?>
<div class="promo-widget-container">
    <div class="promo-widget__overflow"></div>

    <div id="<?= $widgetId ?>" class="promo-widget<?php if ($setting->is_small()): ?> promo-widget--small<?php endif; ?> one-number-lotto">

        <?php if ($setting->background_image): ?>style="background: url(<?= $setting->background_image ?>)"<?php endif; ?>

        <div class="promo-widget__logo">
            <img src="<?= $raffle_image ?>" alt="<?= $raffle->name ?> Logo"/>
        </div>

        <div class="promo-widget__prize">
            <div class="promo-widget__prize-item promo-widget__prize-item--featured">
                <div class="promo-widget__prize-item-text"><?= _('Total prize pool $100') ?></div>
            </div>
            <div class="promo-widget__prize-item"><span class="raffle-name"><?= $raffle->name ?> - </span>
                <span>

                    <br class="promo-widget__prize-item-br">
                    <?= _('Guaranteed winner. Chance of winning 1:100!') ?>
                </span>
            </div>
        </div>

        <div class="promo-widget__button">
            <a href="<?= $setting->button_play_url() ?>" class="btn btn-default btn-xl js-play one-number-lotto-button"><?= _("Play now") ?></a>
        </div>

    </div>
</div>

<?php include(__DIR__ . '/../widgetStyle.php'); ?>
