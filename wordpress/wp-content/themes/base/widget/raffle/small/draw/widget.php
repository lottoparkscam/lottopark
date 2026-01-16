<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

// TODO: {Vordis 2020-05-08 16:35:43} this is stub, it need new widget located in wordpress\wp-content\plugins\lotto-platform\includes\Lotto\Widget\Raffle\Small\Draw.php. Variables below should be initialized there
$raffle = lotto_platform_get_raffle_by_slug($post->post_name);
$raffle_image = Lotto_View::get_lottery_image($raffle['id'], null, 'raffle');
?>

<div class="small-widget small-widget-draw">
    <div class="small-widget-draw-image">
        <img src="<?= UrlHelper::esc_url($raffle_image); ?>" alt="<?= htmlspecialchars(_($raffle['name'])); ?>">
    </div>
    <h2 class="small-widget-draw-title"><a href="/raffle"><?= $raffle['name']; ?></a></h2>
    <div class="small-widget-draw-amount"><?= Lotto_View::format_currency($raffle['main_prize'], $raffle['currency_code']); ?></div>
    <div class="widget-small-draw-button-container">
        <a href="#"
           class="btn btn-primary widget-small-lottery-button play-button"
           data-lottery-slug="<?= $raffle['slug'] ?>"
        >
            <?= _('Play now') ?>
        </a>
    </div>
</div>
