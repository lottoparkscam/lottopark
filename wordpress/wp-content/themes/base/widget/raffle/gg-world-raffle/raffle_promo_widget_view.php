<?php

if (!defined('WPINC')) {
    die;
}

/** @var Lotto_Widget_Raffle_Settings $setting */
$setting;
?>
<div class="promo-widget-container">
    <div class="promo-widget__overflow"></div>

    <div id="<?= $widgetId ?>" class="promo-widget<?php if ($setting->is_small()): ?> promo-widget--small<?php endif; ?>">

        <?php if ($setting->background_image): ?>style="background: url(<?= $setting->background_image ?>)"<?php endif; ?>

        <div class="promo-widget__logo">
            <img src="<?= $raffle_image ?>" alt="Raffle Logo"/>
        </div>

        <div class="promo-widget__prize">
            <div class="promo-widget__prize-item promo-widget__prize-item--featured">
                <div class="promo-widget__prize-item-text"><?= _('Every 100th ticket wins!') ?></div>
            </div>
            <div class="promo-widget__prize-item"><span class="raffle-name"><?= $raffle->name ?> - </span>
                <span><?=sprintf(_('Guaranteed win of %s. <br class="promo-widget__prize-item-br">  The highest odds!'), $prize) ?></span>
            </div>
        </div>

        <div class="promo-widget__button">
            <a href="<?= $setting->button_play_url() ?>" class="btn btn-default btn-xl js-play"><?= _("Play now") ?></a>
        </div>

    </div>
</div>
<?php include(__DIR__ . '/../widgetStyle.php'); ?>