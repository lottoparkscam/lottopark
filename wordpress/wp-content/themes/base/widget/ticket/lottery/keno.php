<?php

// Lottery ticket multipliers
use Carbon\Carbon;
use Helpers\UrlHelper;

$lottery_ticket_multipliers = Model_Lottery::get_multipliers($lottery_id);
$lottery_ticket_has_multipliers = !empty($lottery_ticket_multipliers);

$lottery_numbers_per_line = Lotto_Helper::get_numbers_per_line_array($lottery_id);
$lottery_has_various_numbers_per_line = !empty($lottery_ticket_multipliers);
$lottery_has_various_numbers_per_line = true; //DEBUG - DELETE THIS LINE

$pick_numbers_text = sprintf(_("Pick %s numbers"), '<strong class="pick_numbers_count"></strong>');
?>
<div class="main-width content-width">
    <div class="content-box <?= Lotto_Helper::get_widget_top_area_classes("play-lottery-sidebar-id"); ?>">
        <div class="widget-ticket-wrapper keno-widget-ticket-wrapper">
            <div id="number-selector" class="dialog hidden-normal">
                <div class="number-selector-overlay"></div>
                <div class="dialog-title">
                    <div class="pull-left set-width">
                        <?= sprintf(_("Pick numbers within %s - %s range"), 1, $lottery_type_nrange) ?>
                    </div>
                    <div class="pull-right">
                        <a class="dialog-close" href="#"><span></span></a>
                    </div>
                </div>
                <div class="dialog-content">
                    <div class="dialog-buttons">
                        <?php
                        for ($j = 1; $j <= $lottery_type_nrange; $j++) :
                        ?>
                            <div class="ticket-number-selector-ball-wrapper">
                                <a href="#" class="ticket-number-selector-ball widget-ball"><?= $j ?></a>
                            </div>
                        <?php
                        endfor;
                        ?>
                    </div>
                </div>
            </div>
            <div class="widget-ticket-header-wrapper">
                <div class="widget-ticket-image">
                    <?php
                    if (!empty($lottery_image)) :
                    ?>
                        <img src="<?= UrlHelper::esc_url($lottery_image); ?>" alt="<?= htmlspecialchars(_($lottery_name)); ?>">
                    <?php
                    endif;
                    ?>
                </div>
                <div class="widget-ticket-header">
                    <h1 class="play-lottery" id="play-lottery">
                        <?php the_title(); ?>
                    </h1>
                    <?php
                        $jackpotClassToDynamicUpdate = 'jackpot-to-update-' . $lottery['slug'];
                    ?>
                    <div class="play-lottery-jackpot-amount <?= $jackpotClassToDynamicUpdate ?>">
                        <span style="display: none;"><?= $towin; ?></span>
                        <noindex><span class="loading"></span></noindex>
                    </div>
                    <?php
                    $nextDraw = Lotto_Helper::get_lottery_real_next_draw($lottery);
                    $nextDraw->setTimezone($userTimezone ?? 'UTC');
                    $nextDrawTimestamp = $nextDraw->getTimestamp();
                    $now = Carbon::now($userTimezone);
                    $drawDateDiff = $now->diff($nextDraw);
                    $minNumberWithoutLeadingZero = 10;
                    ?>
                    <time datetime="<?= $nextDrawTimestamp; ?>"
                          class="widget-ticket-time-remain countdown keno-countdown mobile"
                          id="widget-ticket-time-remain-desktop">
                        <span class="draw-in-text"><?= _('Next draw in: ') ?></span>
                        <div class="timer">
                            <div>
                                <span class="time hours" style="display: none">
                                    <span class="loading loading-big"></span>
                                    <span style="display: none">
                                        <?= str_pad($drawDateDiff->h, 2, '0', STR_PAD_LEFT) ?>
                                    </span>
                                </span> <span class="separator-hours" style="display: none">:</span>
                                <span class="time minutes">
                                    <span class="loading loading-big"></span>
                                    <span style="display: none">
                                        <?= $drawDateDiff->i < $minNumberWithoutLeadingZero ? '0' . $drawDateDiff->i : $drawDateDiff->i ?>
                                    </span>
                                </span> :
                                <span class="time seconds">
                                     <span class="loading loading-big"></span>
                                    <span style="display: none">
                                        <?= $drawDateDiff->s < $minNumberWithoutLeadingZero ? '0' . $drawDateDiff->s : $drawDateDiff->s ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </time>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="widget-ticket-buttons-all">
                <time datetime="<?= $nextDrawTimestamp; ?>"
                      class="widget-ticket-time-remain mobile-hidden countdown keno-countdown"
                      id="widget-ticket-time-remain-desktop">
                    <span class="draw-in-text"><?= _('Next draw in: ') ?></span>
                    <div class="timer">
                        <div>
                            <span class="time hours" style="display: none">
                                <span class="loading loading-big"></span>
                                <span style="display: none">
                                    <?= str_pad($drawDateDiff->h, 2, '0', STR_PAD_LEFT) ?>
                                </span>
                            </span> <span class="separator-hours" style="display: none">:</span>
                            <span class="time minutes">
                                <span class="loading loading-big"></span>
                                <span style="display: none">
                                    <?= $drawDateDiff->i < $minNumberWithoutLeadingZero ? '0' . $drawDateDiff->i : $drawDateDiff->i ?>
                                </span>
                            </span> :
                            <span class="time seconds">
                                <span class="loading loading-big"></span>
                                <span style="display: none">
                                    <?= $drawDateDiff->s < $minNumberWithoutLeadingZero ? '0' . $drawDateDiff->s : $drawDateDiff->s ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </time>
            </div>
            <div class="clearfix"></div>
            <form method="post" action="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('order')); ?>" autocomplete="off" id="widget-ticket-form">
                <input type="hidden" autocomplete="nope" name="order[lottery]" value="<?= $lottery_id; ?>" id="orderLotteryId">
                <input id="order-multidraw-enabled" type="hidden" name="order[multidraw_enabled]" value="0">
                <div class="widget-ticket-options d-flex">
                    <?php if ($lottery_has_various_numbers_per_line) : ?>
                        <div class="widget-ticket-additional-form widget-ticket-options-slip-size w-50">
                            <div class="widget-ticket-option-top">
                                <div class="widget-ticket-option-select-wrapper">
                                    <select name="order[numbers_per_line]" id="widget-ticket-slip-size-select">
                                        <?php foreach ($lottery_numbers_per_line as $number_per_line) : ?>
                                            <option value="<?= $number_per_line ?>" data-slip-size="<?= $number_per_line ?>">
                                                <?= Security::htmlentities(sprintf("%s %s", $number_per_line, _n('number', _('numbers'), $number_per_line))) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="widget-ticket-options-description widget-ticket-options-description-mobile widget-ticket-options-slip-size-bottom">
                                    <span><?= str_replace(['pick 1', '10', '70'], ['pick ' . min($lottery_numbers_per_line), max($lottery_numbers_per_line), $lottery_type_nrange], _("You can pick 1 - 10 numbers within 1 - 70 range")) ?></span>
                                </div>
                                <h3><?= _("How many numbers would you like to play?") ?></h3>
                            </div>
                            <div class="widget-ticket-options-description widget-ticket-options-slip-size-bottom">
                                <span><?= str_replace(['pick 1', '10', '70'], ['pick ' . min($lottery_numbers_per_line), max($lottery_numbers_per_line), $lottery_type_nrange], _("You can pick 1 - 10 numbers within 1 - 70 range")) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($lottery_ticket_has_multipliers) : ?>
                        <div class="widget-ticket-additional-form widget-ticket-options-stakes w-50">
                            <div class="widget-ticket-option-top">
                                <div class="widget-ticket-option-select-wrapper">
                                    <select name="order[ticket_multiplier]" id="widget-ticket-stake-select">
                                        <?php foreach ($lottery_ticket_multipliers as $ticket_multiplier) : ?>
                                            <option value="<?= $ticket_multiplier['multiplier'] ?>" data-stake="<?= $ticket_multiplier['multiplier'] ?>">
                                                <?= Security::htmlentities(sprintf("%s%s", _('x'), $ticket_multiplier['multiplier'])) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="widget-ticket-options-description widget-ticket-options-description-mobile widget-ticket-options-slip-size-bottom">
                                    <span>
                                        <?= str_replace(
                                                [' 10 ', ' x10 '],
                                                [' ' . max($lottery_numbers_per_line) . ' ', ' x' . max(array_column($lottery_ticket_multipliers, 'multiplier')) . ' '],
                                                sprintf(_("The higher the stake, the bigger the win. You can win up to %s while picking 10 numbers with x10 stake"), $kenoMaxPrize)
                                            ) ?>
                                    </span>
                                </div>
                                <h3><?= _("Choose a stake") ?> (<?= _("Multiplier") ?>)</h3>
                            </div>
                            <div class="widget-ticket-options-description widget-ticket-options-slip-size-bottom">
                                <span>
                                    <?= str_replace(
                                            [' 10 ', ' x10 '],
                                            [' ' . max($lottery_numbers_per_line) . ' ', ' x' . max(array_column($lottery_ticket_multipliers, 'multiplier')) . ' '],
                                            sprintf(_("The higher the stake, the bigger the win. You can win up to %s while picking 10 numbers with x10 stake"), $kenoMaxPrize)
                                        ) ?>
                                    </span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="widget-ticket-content widget-ticket-content-horizontal"
                    <?php if ($lottery_multiplier != 0) : echo ' data-multiplier=' . $lottery_multiplier; endif; ?>
                     data-nrange="<?= $lottery_type_nrange; ?>"
                     data-ncount="<?= $lottery_type_ncount; ?>"
                     data-brange="<?= $lottery_type_brange; ?>"
                     data-bcount="<?= $lottery_type_bcount; ?>"
                     data-currency=""
                     data-currencycode=""
                     data-format=""
                     data-price=""
                     data-min="<?= intval($minimum_lines); ?>"
                     data-min_bets="<?= $lottery_min_bets; ?>"
                     data-max_bets="<?= $lottery_max_bets; ?>"
                     data-numbers_per_line="<?= max($lottery_numbers_per_line) ?>"
                     data-multidraw_enabled="0"
                >
                    <?= $title; ?>
                    <input type="hidden" autocomplete="nope" name="order[lines]" id="widget-ticket-input" value="">
                    <div class="widget-ticket-entity widget-ticket-entity-horizontal relative d-flex">
                        <div class="widget-ticket-icon-ok">
                            <span class="fa fa-check-circle" aria-hidden="true"></span>
                        </div>
                        <div class="widget-ticket-entity-content d-inline-flex">
                            <div class="widget-ticket-entity-help-wrapper">
                                <span class="widget-ticket-entity-help"><?= $pick_numbers_text; ?></span>
                                <span class="widget-ticket-entity-ok">
                                    <?= _("OK") ?>
                                </span>
                            </div>
                            <div class="widget-ticket-numbers">
                                <a href="#" class="widget-ticket-number-selector widget-ball">
                                    <!--
                                    -->
                                    <div class="widget-ticket-number-value"></div>
                                    <!--
                                -->
                                </a>
                            </div>
                        </div>
                        <div class="widget-ticket-buttons-visible d-inline-flex">
                            <button type="button" class="btn btn-tertiary widget-ticket-button-quickpick-horizontal">
                                <?= _('Quick Pick') ?>
                            </button>
                            <button type="button" class="btn btn-tertiary widget-ticket-button-clear">
                                <span class="fa fa-solid fa-trash-can" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="widget-ticket-bottom d-flex">
                    <?php if ($lottery_multi_draws_options) : ?>
                        <div class="w-50 pull-left widget-ticket-additional-form-box">
                            <div class="platform-form">
                                <span class="widget-ticket-additional-form-header"><?= _("Choose the type of ticket") ?></span>
                                <div class="widget-ticket-additional-form
                                            <?php
                                            if (!empty($group_lotteries)) :
                                            ?>
                                                    widget-ticket-additional-form-first
                                            <?php
                                            endif;
                                            ?>">

                                    <div class="form-group">
                                        <label><input type="radio" name="ticket_type" id="ticket_type" value="1" checked="checked"> <?= _("Single ticket") ?>
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label><input type="radio" name="ticket_type" id="ticket_type" value="2"> <?= _("Multi-draws") ?>
                                        </label>
                                        <select name="multi_draw_type" id="inputMultiDraw">
                                            <?php
                                            foreach ($lottery_multi_draws_options as $option) :
                                            ?>
                                                <option value="<?= $option['id']; ?>" data-tickets="<?= $option['tickets']; ?>" data-discount="<?= $option['discount']; ?>"><?= $option['tickets']; ?> <?= _("draws") ?>
                                                    (<?= $option['discount']; ?>% <?= _("discount") ?>)
                                                </option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php
                    endif; ?>
                </div>
                <div class="clearfix"></div>
                <div class="widget-ticket-buttons-bottom-wrapper">
                    <div class="widget-ticket-buttons-bottom widget-ticket-buttons-bottom-keno">
                        <button type="button" class="btn btn-tertiary widget-ticket-button-more-horizontal keno-line-price-to-update-<?= $lottery['slug'] ?>">
                            <i class="fa fa-plus-circle"></i>
                            <?= sprintf(_("Add another line for <strong>%s</strong>"), ''); ?>
                        </button>
                    </div>
                    <div class="widget-ticket-summary">
                        <div class="widget-ticket-summary-content-total pull-left">
                            <span class="widget-ticket-summary-content-header"><?= _("Sum") ?>:</span>
                            <span class="widget-ticket-summary-content-total-value">
                                <span class="sum-container">
                                    <span class="loading"></span>
                                </span>
                            </span>
                        </div>
                        <button type="submit" disabled="disabled" class="btn btn-primary pull-left widget-ticket-summary-button btn-mobile-large" id="play-continue">
                            <?= _('Continue') ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <a href="https://access.gaminglabs.com/certificate/index?i=314" class="gli-certificate-box row mx-0" rel="nofollow" target="_blank">
            <div class="gli-certificate-img-wrapper">
                <img src="<?= get_template_directory_uri() . '/images/widgets/gli/gli-check.png' ?>" alt="Gaming Labs Certified">
            </div>
            <div class="col gli-certificate-text-wrapper">
                <p class="text-primary">
                    <strong><?= _("Draw certified by Gaming Laboratories International") ?></strong>
                </p>

                <p><small><?= _("Our True Random Number Generator has been certified by Gaming Laboratories International to ensure the highest security and guarantee 100% fairness of the drawing process.") ?></small></p>
            </div>
            <div class="gli-certificate-img-wrapper">
                <img src="<?= get_template_directory_uri() . '/images/widgets/gli/gli.jpg' ?>" alt="Gaming Labs Certified">
            </div>
        </a>
        <?php $whitelabel = Lotto_Settings::getInstance()->get("whitelabel"); ?>
        <h2><a href="https://<?= $whitelabel['domain'] ?>/how-does-trng-work/" target="_blank">Click here to see how does the TRNG work</a></h2>
    </div>
</div>
<script>
  // Sending of the "view_item" event
  // See commented method: @see \Events_User_Item_View::run
  if (window.dataLayer && Array.isArray(window.dataLayer)) {
    window.dataLayer.push(<?php echo json_encode($viewItemData); ?>);
  }
</script>
