<?php

use Helpers\UrlHelper;

?>
<div class="main-width content-width">
    <div class="content-box <?= Lotto_Helper::get_widget_top_area_classes("play-lottery-sidebar-id"); ?>">
        <div class="widget-ticket-wrapper">
            <div class="widget-ticket-header-wrapper">
                <?php
                if (empty($group_lotteries)) {
                    $lottery_header_title = get_the_title();
                    include(__DIR__ . "/../lottery_header.php");
                } else {
                    foreach ($group_lotteries as $group_lottery) {
                        if (!$group_lottery['is_enabled'] || $group_lottery['is_temporarily_disabled']) {
                            continue;
                        }
                        $lottery_image = Lotto_View::get_lottery_image($group_lottery['id']);
                        $lottery_name = $group_lottery['name'];
                        $lottery_header_title = get_the_title();
                        $towin = $group_lottery['towin'];
                        $draw_in_human_time_escaped = $group_lottery['draw_in_human_time_escaped'];
                        $glottery = $lotteries["__by_id"][$group_lottery["lottery_id"]];
                        $glottery_next_draw = Lotto_Helper::get_lottery_real_next_draw($glottery);
                        $glottery_next_draw->setTimezone(Lotto_Settings::getInstance()->get("timezone") ?? 'UTC');
                        $group_lottery_next_draw_timestamp = $glottery_next_draw->getTimestamp();

                        if ($lottery['id'] === $group_lottery['id']) {
                            echo '<div class="group_play_title">';
                            include(__DIR__ . "/../lottery_header.php");
                            echo '</div>';
                        } else {
                            $lottery_post_id = lotto_platform_get_post_id_by_slug("play/" . $group_lottery['slug']);
                            $lottery_post = get_post($lottery_post_id);
                            echo '<div class="group_play_title hidden-normal">';
                            if (!empty($lottery_post) && isset($lottery_post->post_title)) {
                                $lottery_header_title = apply_filters('the_title', $lottery_post->post_title);
                                include(__DIR__ . "/../lottery_header.php");
                            }
                            echo '</div>';
                        }
                    }
                }
                ?>
                <div class="widget-ticket-buttons-all">
                    <?php
                    $next_draw = Carbon\Carbon::createFromTimestamp($next_draw_timestamp);
                    $now = Carbon\Carbon::now();
                    $draw_date_diff = $now->diff($next_draw);
                    $classToDynamicUpdate = 'next-real-draw-timestamp-to-update-' . $lottery['slug'];
                    ?>
                    <time datetime="<?= $next_draw_timestamp; ?>" class="widget-ticket-time-remain mobile-show countdown <?= $classToDynamicUpdate ?>" id="widget-ticket-time-remain-mobile">
                        <span class="fa fa-clock-o" aria-hidden="true"></span>
                        <span class="draw-in-text"><?= _('Draw in ') ?></span>
                        <?php
                        if (($days = $draw_date_diff->d) > 0) : ?>
                            <span class="time days">
                                <span style="display: none;"><?= $draw_date_diff->d ?></span>
                                <noindex><span class="loading"></span></noindex>
                            </span>
                            <?= _('days') ?>
                        <?php endif; ?>
                        <span class="time hours">
                                <span style="display: none;"><?= $draw_date_diff->h ?></span>
                                <noindex><span class="loading"></span></noindex>
                        </span>
                        <?= _('hours') ?>
                        <span class="time minutes">
                            <span style="display: none;"><?= $draw_date_diff->i ?></span>
                                <noindex><span class="loading"></span></noindex>
                        </span>
                        <?= _('minutes') ?>
                        <?php if ($days <= 0) : ?>
                            <span class="time seconds">
                                <span style="display: none;"><?= $draw_date_diff->i ?></span>
                                <noindex><span class="loading"></span></noindex>
                            </span>
                            <?= _('seconds') ?>
                        <?php endif; ?>
                    </time>

                    <div class="buttons-wrapper">
                        <button type="button" autocomplete="off" class="btn btn-secondary widget-ticket-quickpick-all">
                            <?= _("Quick Pick All") ?>
                        </button>
                        <button type="button" autocomplete="off" class="btn btn-secondary widget-ticket-clear-all" disabled title="<?= _('Clear All') ?>">
                            <span class="fa fa-solid fa-trash-can" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="clearfix"></div>

            <?php
            if (!empty($group_lotteries)) :
            ?>
                <div class="widget-ticket-alerts-groups">
                    <?php
                    foreach ($group_lotteries as $glottery) :
                        if (!isset($lotteries["__by_id"][$glottery["id"]])) {
                            continue;
                        }
                        $class = "";
                        if ($glottery['id'] != $lottery['id']) : $class = " hidden-normal";
                        endif;
                    ?>
                        <div class="widget-ticket-alerts-group<?= $class; ?>">
                            <?php
                            $warnings = $glottery['warnings'];
                            include(__DIR__ . '/../warnings.php');
                            ?>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
            <?php
            else :
                $warnings = Lotto_Helper::get_warnings_for_lottery($lottery);
                include(__DIR__ . '/../warnings.php');
            endif;

            $baseUrl = lotto_platform_get_permalink_by_slug('order');
            ?>
            <div class="small-purchase-section">
                <a href="<?= $baseUrl ?>" data-count="" class="small-purchase first-small-purchase-to-update">
                    <span class="small-purchase-description">
                        <span class="small-purchase-description-primary-text">
                            <span class="loading"></span>
                        </span>
                        <span class="small-purchase-description-secondary-text">
                            <span class="loading"></span>
                        </span>
                    </span>
                </a>
                <a href="<?= $baseUrl ?>" data-count="" class="small-purchase second-small-purchase-to-update">
                    <span class="small-purchase-description">
                        <span class="small-purchase-description-primary-text">
                            <span class="loading"></span>
                        </span>
                        <span class="small-purchase-description-secondary-text">
                            <span class="loading"></span>
                        </span>
                    </span>
                </a>
                <div class="clearfix"></div>
            </div>
            <form method="post" action="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('order')); ?>" autocomplete="off" id="widget-ticket-form">
                <input type="hidden" autocomplete="nope" name="order[lottery]" value="<?= $lottery_id; ?>" id="orderLotteryId">
                <input id="order-multidraw-enabled" type="hidden" name="order[multidraw_enabled]" value="0">
                <div
                    class="widget-ticket-content"
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
                    data-multidraw_enabled="0"
                >
                    <?= $title; ?>
                    <input type="hidden" autocomplete="nope" name="order[lines]" id="widget-ticket-input" value="">

                    <?php for ($j = 0; $j < Lotto_Widget_Ticket::ENTITIES_COUNT; $j++) : ?>
                        <div class="widget-ticket-entity relative<?php if ($j >= 5) : echo ' hidden';
                                                                    endif; ?> mobile-hidden" data-i="<?= $j; ?>">
                            <div class="widget-ticket-mobile-close">
                                <div class="pull-right">
                                    <a class="dialog-close" href="#"><span></span></a>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="widget-ticket-entity-content">
                                <div class="widget-ticket-buttons">
                                    <button type="button" class="btn btn-xs btn-tertiary widget-ticket-button-clear" disabled>
                                        <?= _('Clear') ?>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-tertiary widget-ticket-button-quickpick">
                                        <?= _('Quick Pick') ?>
                                    </button>
                                </div>
                                <div class="widget-ticket-icon-ok">
                                    <span class="fa fa-check-circle" aria-hidden="true"></span>
                                </div>
                                <span class="widget-ticket-entity-help"><?= $pick_numbers_text; ?></span>
                                <span class="widget-ticket-entity-ok">
                                    <?= _('OK') ?>
                                </span>
                                <span class="widget-ticket-entity-processing">
                                    <?= _('Processing...') ?>
                                </span>
                                <div class="clearfix"></div>
                                <div class="widget-ticket-numbers">
                                    <?php
                                    for ($i = 1; $i <= $lottery_type_nrange; $i++) :
                                    ?><a class="widget-ticket-number" href="#"><?= $i; ?></a><?php
                                                                                            endfor;
                                                                                                ?>
                                </div>
                                <?php
                                if ($lottery_type_bcount > 0) :
                                    $b = 1;
                                ?>
                                    <div class="widget-ticket-bnumbers">
                                        <?php
                                        for ($i = $b; $i <= $lottery_type_brange; $i++) :
                                        ?><a class="widget-ticket-number" href="#"><?= $i; ?></a><?php
                                                                                                endfor;
                                                                                                    ?>
                                    </div>
                                <?php
                                endif;
                                ?>
                                <div class="widget-ticket-mobile-button">
                                    <a href="#" class="btn btn-primary disabled btn-mobile-large">
                                        <?= _('Confirm') ?>
                                    </a>
                                </div>
                            </div>
                        </div><?php
                            endfor;
                                ?>

                    <?php
                    for ($j = 0; $j < 25; $j++) :
                    ?>
                        <div class="widget-ticket-entity-mobile<?php if ($j >= 1) : echo ' mobile-hidden';
                                                                endif; ?>" data-i="<?= $j; ?>">
                            <a class="widget-ticket-entity-newline" href="#" rel="nofollow"><?= _("+ Add new line") ?></a>
                            <a class="widget-ticket-entity-editline" href="#" rel="nofollow"><?= _("+ Edit line") ?></a>
                            <a href="#" rel="nofollow" class="btn btn-tertiary btn-xs widget-ticket-entity-mobile-delete" title="<?= _('Clear') ?>"><span class="fa fa-solid fa-trash-can" aria-hidden="true"></span></a>
                            <a href="#" rel="nofollow" class="btn btn-tertiary btn-xs widget-ticket-entity-mobile-quickpick"><?= _('Quick Pick') ?></a>
                        </div>
                    <?php
                    endfor;
                    if ($lottery_multi_draws_options) :
                    ?>
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
                                                <option
                                                        value="<?= $option['id']; ?>"
                                                        data-tickets="<?= $option['tickets']; ?>"
                                                        data-discount="<?= $option['discount']; ?>"
                                                >
                                                    <?= $option['tickets']; ?> <?= _("draws") ?>(<?= $option['discount']; ?>% <?= _("discount") ?>)
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
                    endif;
                    ?>
                    <?php if (!empty($group_lotteries)) : ?>
                        <div class="w-50 pull-left text-left widget-ticket-additional-form-box">
                            <div class="platform-form">
                                <span class="widget-ticket-additional-form-header"><?= _('Choose the type of game you wish to play') ?></span>
                                <div class="widget-ticket-additional-form">
                                    <?php foreach ($group_lotteries as $key => $glottery) : ?>
                                        <?php
                                        if (!isset($lotteries["__by_id"][$glottery["id"]])) {
                                            continue;
                                        }
                                        ?>
                                        <div class="form-group">
                                            <label>
                                                <input
                                                        type="radio"
                                                        data-index="<?= $key; ?>"
                                                        name="group_lottery"
                                                        class="widget-ticket-group-lottery"
                                                        value="<?= $glottery["lottery_id"]; ?>"
                                                    <?= $glottery['lottery_id'] == $lottery['id'] ? ' checked="checked"' : ''; ?>
                                                    <?= implode(" ", $glottery['additional_fields']); ?>
                                                > <?= Security::htmlentities(_($lotteries["__by_id"][$glottery['lottery_id']]["name"])) ?>
                                                (<span class="jackpot-to-update-<?= $lotteries["__by_id"][$glottery['lottery_id']]['slug'] ?>"><?= $glottery['towin'] ?></span>
                                                <?= sprintf(_("%s jackpot"), ''); ?>)
                                                <strong>- <span class="line-price-to-update-<?= $lotteries["__by_id"][$glottery['lottery_id']]['slug'] ?>">
                                                        <?= $glottery['pricing'] ?>
                                                    </span> per line
                                                </strong></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                    <div class="widget-ticket-button-wrapper">
                        <div class="widget-ticket-buttons-bottom">
                            <div class="pull-left">
                                <button type="button" class="btn btn-sm btn-tertiary widget-ticket-button-less" disabled="disabled">
                                    <?= _('Fewer Lines -') ?>
                                </button>
                                <button type="button" class="btn btn-sm btn-tertiary widget-ticket-button-more">
                                    <?= _('More Lines +') ?>
                                </button>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="pull-right widget-ticket-summary">
                            <div class="widget-ticket-summary-content-total pull-left">
                                <span class="widget-ticket-summary-content-header"><?= _('Sum') ?>:</span>
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
                    <div class="clearfix"></div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
  // Sending of the "view_item" event
  // See commented method: @see \Events_User_Item_View::run
  if (window.dataLayer && Array.isArray(window.dataLayer)) {
    window.dataLayer.push(<?php echo json_encode($viewItemData); ?>);
  }
</script>
