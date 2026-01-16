<?php

                                                                                                    use Models\RaffleRuleTier;

if (!defined('WPINC')) {
    die;
}

/** @var Lotto_Widget_Raffle_Settings $setting */
$setting;

                                                                                                    // filter for tier in prizes
                                                                                                    /** @var RaffleRuleTier[] $tiers_in_kind */
                                                                                                    $tiers_in_kind = array_filter($raffle->getFirstRule()->tiers, function (RaffleRuleTier $tier) {
    return !empty($tier->tier_prize_in_kind);
});
$tier_in_kind = array_shift($tiers_in_kind);
$first_tier_prize_text = $tier_in_kind->tier_prize_in_kind->name;

$prize_image = sprintf('/wp-content/plugins/lotto-platform/public/images/raffle/prize-in-kind/%s.png', $tier_in_kind->tier_prize_in_kind->slug);
?>
<div class="promo-widget-container">
    <div class="lk-widget">
        <a href="<?= $setting->button_play_url() ?>" class="overflow"> </a>

        <div class="logo">
            <img class="large-only" src="<?= $prize_image ?>" alt="Prize Image"/>
            <img class="mobile-only" src="<?= $raffle_image ?>" alt="Raffle Image"/>
        </div>

        <div class="content">
            <div class="text">
                <div class="text-featured"><?= _('Total prize pool') ?> <?= Lotto_View::format_currency($raffle->prizes_sum, $raffle->currency->code) ?></div>
                <div class="text-regular">
                    <?= sprintf(_('First prize: brand new %s. Nearly every 6th ticket wins.'), $first_tier_prize_text) ?>
                </div>
            </div>

            <div class="button">
                <a href="<?= $setting->button_play_url() ?>" class="btn btn-default btn-lg js-play"><?= _("Play now") ?></a>
            </div>
        </div>
    </div>
</div>

