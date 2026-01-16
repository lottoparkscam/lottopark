<div id="keno-animation">

    <div class="ka-header">
        <img class="ka-img" src="<?= $args['img-ball'] ?>" alt="">
        <div id="header" class="ka-countdown">
            <div id="timer" class="widget-ticket-time-remain countdown keno-countdown" datetime="<?= $args['next-draw-timestamp'] ?>" repeatMoreThanOnce="false">
                <div class="ka-text-draw"><?= _('Next draw starts in') ?></div>
                <div class="ka-timer">
                    <span id="hours" class="time hours" style="display: none"><?= str_pad($args['timer-data']->h, 2, '0', STR_PAD_LEFT) ?></span>
                    <span class="separator-hours" style="display: none">:</span>
                    <span id="minutes" class="time minutes"><?= str_pad($args['timer-data']->i, 2, '0', STR_PAD_LEFT) ?></span>
                    <span>:</span>
                    <span id="seconds" class="time seconds"><?= str_pad($args['timer-data']->s, 2, '0', STR_PAD_LEFT) ?></span>
                </div>
            </div>
        </div>
        <div class="ka-btn-group">
            <a class="ka-btn" href="<?= $args['link-page-play'] ?>" role="button">
                <span><?= _('Play again') ?></span>
                <i class="fa fa-rotate-right"></i>
            </a>
        </div>
    </div>

    <div class="ka-body">

        <div class="ka-results">
            <div id="ka-results" class="ka-row">
                <?php for ($i = 1; $i <= $args['numbers-drawn-count']; $i++): ?>
                    <div class="ka-col">
                        <div class="ka-ball" data-number=""></div>
                    </div>
                <?php endfor;?>
            </div>
        </div>

        <div class="ka-canvas-container">
            <canvas id="ka-canvas" width="800" height="420" data-enabled="<?= $args['is-enabled'] ?>"></canvas>
        </div>

    </div>

</div>
