<?php

use Models\Raffle;
use Helpers\UrlHelper;
use Models\WhitelabelRaffle;

$whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

if (empty($lottery)) {
    return;
}

$lottery_explode = explode('_', $lottery);
$lottery_type = $lottery_explode[0];
$lottery_slug = $lottery_explode[1];
$is_raffle = $lottery_type === 'raffle';


if ($is_raffle)
{
    $lottery = Raffle::find("first", [
        'where' => [
            'slug' => $lottery_slug
        ]
    ]);

    $whitelabel_lottery = WhitelabelRaffle::find("first", [
        'where' => [
            'whitelabel_id' => $whitelabel['id'],
            'raffle_id' => $lottery['id']
        ]
    ]);
} else {
    $lottery = Model_Lottery::find_one_by('slug', $lottery_slug);
    $whitelabel_lottery = Model_Whitelabel_Lottery::find_one_by([
        'whitelabel_id' => $whitelabel['id'],
        'lottery_id' => $lottery['id']
    ]);
}

$main_lottery_is_enabled = !empty($lottery) && !empty($whitelabel_lottery) && $whitelabel_lottery['is_enabled'];
?>
<div style="background: #f4f4f4; margin-bottom: 2rem;" class="small-widget small-widget-draw">
    <?php
    if ($main_lottery_is_enabled):
        $prefix = $is_raffle ? 'raffle' : 'lottery';
        $lottery_image = Lotto_View::get_lottery_image($lottery['id'], null, $prefix);

        $permalink_slug = $is_raffle ? 'play-raffle' : 'play';
        $play_info_href = lotto_platform_get_permalink_by_slug($permalink_slug) . $lottery['slug'];
        $lottery_is_playable = $is_raffle || (isset($lottery['playable']) && $lottery['playable']);
        ?>
        <div class="small-widget-draw-image">
            <img src="<?= UrlHelper::esc_url($lottery_image); ?>"
                 alt="<?= htmlspecialchars(_($lottery['name'])); ?>">
        </div>

        <h2 class="small-widget-draw-title">
            <a href="<?= UrlHelper::esc_url($play_info_href); ?>">
                <?= empty($title) ? _($lottery['name']) : $title; ?>
            </a>
        </h2>

        <div class="small-widget-draw-amount jackpot-to-update-<?= $is_raffle ? 'raffle-' : '' ?><?= $lottery_slug ?>">
            <span class="loading"></span>
        </div>

        <?php if (!$is_raffle): ?>
            <time class="widget-ticket-time-remain next-real-draw-short-to-update-<?= $lottery_slug ?>">
                <span class="fa fa-clock-o" aria-hidden="true"></span>
                <span class="loading"></span>
            </time>
        <?php endif; ?>

        <div class="widget-small-draw-button-container">
            <a href="<?= UrlHelper::esc_url($play_info_href); ?>"
               class="btn btn-primary widget-small-lottery-button play-button" data-lottery-slug="<?= $lottery_slug ?>">
                <?= _('Play now') ?>
            </a>
        </div>

    <?php endif; ?>
</div>
