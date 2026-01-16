<?php

if (!defined('WPINC')) {
    die;
}

/** @var Lotto_Widget_Raffle_Settings $setting */
$setting;
?>
<div class="promo-widget-container">
    <div class="lk-widget">
        <a href="<?= $setting->button_play_url() ?>" class="overflow"> </a>

        <div class="logo">
            <img src="<?= $raffle_image ?>" alt="<?= $raffle->name ?> Logo"/>
        </div>

        <div class="content">
            <div class="text">
                <div class="text-featured"><?= _('Total prize pool') ?> <?= Lotto_View::format_currency($raffle->prizes_sum, $raffle->currency->code) ?></div>
                <div class="text-regular">
                    <?= _('Nearly every 3nd ticket wins. Jackpot odds 1:1000!') ?>
                </div>
            </div>

            <div class="button">
                <a href="<?= $setting->button_play_url() ?>" class="btn btn-default btn-lg js-play"><?= _("Play now") ?></a>
            </div>
        </div>
    </div>
</div>
