<div class="widget-ticket-image">
    <?php

    use Helpers\UrlHelper;

    if (!empty($lottery_image)):
    ?>
            <img src="<?= UrlHelper::esc_url($lottery_image); ?>"
                 alt="<?= htmlspecialchars(_($lottery_name)); ?>">
    <?php
        endif;
        $nextDrawClassToDynamicUpdate = 'next-real-draw-short-to-update-' . $lottery['slug'];
        $jackpotClassToDynamicUpdate = 'jackpot-to-update-' . $lottery['slug'];
    ?>
</div>
<div class="widget-ticket-header">
    <h1 class="play-lottery" id="play-lottery">
        <?= $lottery_header_title; ?>
    </h1>
    <div class="play-lottery-jackpot-amount <?= $jackpotClassToDynamicUpdate ?>">
        <span style="display: none;"><?= $towin; ?></span>
        <noindex><span class="loading"></span></noindex>
    </div>
    <time datetime="<?= isset($group_lottery_next_draw_timestamp) ? $group_lottery_next_draw_timestamp : $next_draw_timestamp; ?>"
          class="widget-ticket-time-remain mobile-show <?= $nextDrawClassToDynamicUpdate ?>">
        <span class="fa fa-clock-o" aria-hidden="true"></span>
        <span style="display: none;"><?= $draw_in_human_time_escaped; ?></span>
        <noindex><span class="loading"></span></noindex>
    </time>
</div>