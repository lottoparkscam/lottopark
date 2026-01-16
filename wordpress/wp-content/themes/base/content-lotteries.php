<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

$page_posts = apply_filters('wpml_object_id', get_option('page_for_posts'), 'page', false);

list(
    $social_share_rows,
    $counter_socials,
    $current_url
    ) = Helpers_General::get_prepared_social_share_links();
?>
<div class="content-area <?php echo (basename(get_permalink()) === basename(UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("keno-lotteries")))) ? 'page-keno-lotteries-show-only-keno' : null; ?> <?php
echo Lotto_Helper::get_widget_main_area_classes(null, "info-more-sidebar-id");
?>">
    <?php
    get_content_main_menu($page_posts, 'lotteries');

    get_active_sidebar('info-sidebar-id');
    ?>
    <div class="main-width content-width">
        <div class="content-box<?php
        echo Lotto_Helper::get_widget_top_area_classes("info-sidebar-id");
        echo Lotto_Helper::get_widget_bottom_area_classes("info-more-sidebar-id");
        ?>">
            <section class="page-content">
                <article class="page">
                    <h1><?php the_title(); ?></h1>
                    <?php
                    $post_data = get_extended($post->post_content);
                    echo apply_filters('the_content', $post_data['main']);
                    ?>
                </article>
            </section>

            <?php
            /*** old widget info area ***/

            $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
            $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
            ?>
            <div class="info-content">
                <?php
                if (!empty($lotteries) && count($lotteries) > 0):
                    ?>
                    <div class="mobile-only pull-right">
                        <label for="info-mobile-sort" class="table-sort-label">
                                <?= _('Sort by') ?>:
                        </label>
                        <select id="info-mobile-sort" class="info-mobile-sort">
                            <option value="2_0">
                                <?= _('Lottery') ?> - <?= _('from A to Z') ?>
                            </option>
                            <option value="2_1">
                                <?= _('Lottery') ?> - <?= _('from Z to A') ?>
                            </option>
                            <option value="3_0" selected="selected">
                                <?= _('Draw') ?> - <?= _('by oldest') ?>
                            </option>
                            <option value="3_1">
                                <?= _('Dra') ?> - <?= _('by newest') ?>
                            </option>
                            <option value="5_1">
                                <?= _('Winning Odd') ?> - <?= _('by lowest') ?>
                            </option>
                            <option value="5_0">
                                <?= _('Winning Odds') ?> - <?= _('by highest') ?>
                            </option>
                            <option value="6_0">
                                <?= _('Estimated Jackpot') ?> - <?= _('by lowest') ?>
                            </option>
                            <option value="6_1">
                                <?= _('Estimated Jackpot') ?> - <?= _('by highest') ?>
                            </option>
                        </select>
                    </div>

                    <div class="clearfix"></div>

                        <table class="table table-info table-hover tablesorter" id="info-table">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _('Country') ?>
                                    </th>
                                    <th class="info-lottery-image"></th>
                                    <th>
                                        <?= _('Lottery') ?>
                                    </th>
                                    <th>
                                        <?= _('Draw') ?>
                                    </th>
                                    <th>
                                        <?= _('Guess Range') ?>
                                    </th>
                                    <th>
                                        <?= _('Winning Odds') ?>
                                    </th>
                                    <th>
                                        <?= _('Estimated Jackpot') ?>
                                    </th>
                                    <th>
                                        &nbsp;
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach ($lotteries['__sort_nextdate'] as $key => $lottery):
                                        $is_lottery_closed = Lotto_Helper::is_lottery_closed($lottery, null, $whitelabel);

                                        $param_next = 1;
                                        if ($is_lottery_closed) {
                                            $param_next = 2;
                                        }
                                        $real_next_draw_temp = Lotto_Helper::get_lottery_real_next_draw($lottery, $param_next);
                                        $real_next_draw = $real_next_draw_temp->format('Y-m-d');
                                        $lottery_type = Model_Lottery_Type::get_lottery_type_for_date($lottery, $real_next_draw);

                                        $country_flag = 'none';
                                        if (!empty($lottery['country'])) {
                                            $country_flag = Lotto_View::map_flags($lottery['country']);
                                        }

                                        $lottery_image = Lotto_View::get_lottery_image($lottery['id']);

                                        $lottery_info_href = lotto_platform_get_permalink_by_slug('lotteries/' . $lottery['slug']);

                                        $last_timestamp = Lotto_View::get_last_UTC_timestamp($lottery['next_date_local'], $lottery['timezone']);

                                        $full_next_date_local = $lottery['next_date_local'];

                            $next_date_local = Lotto_View::format_date(
                                $full_next_date_local,
                                IntlDateFormatter::MEDIUM,
                                IntlDateFormatter::NONE,
                                $lottery['timezone'],
                                false
                            );

                            if (Helpers_Lottery::is_keno($lottery)) {
                                $lottery_numbers_per_line = Lotto_Helper::get_numbers_per_line_array($lottery['id']);
                                $guess_range = min($lottery_numbers_per_line) . ' &ndash; ' . max($lottery_numbers_per_line) . ' / ' . $lottery_type['nrange'];
                                $odds_formatted = 0;
                                $winning_odds = sprintf(_("-"));
                            } else {
                                $guess_range = $lottery_type['ncount'] . '/' . $lottery_type['nrange'];
                                if ($lottery_type['bcount'] > 0 && $lottery_type['bextra'] == 0) {
                                    $guess_range .= ' + ' . $lottery_type['bcount'] . '/' . $lottery_type['brange'];
                                }
                                $odds_formatted = Lotto_View::format_number($lottery_type['odds']);
                                $winning_odds = sprintf(_("1 in %s"), $odds_formatted);
                            }

                            $currenct_jackpot = $lottery['current_jackpot'] * 1000000;
                            $currentJackpotUsd = $lottery['current_jackpot_usd'] * 1000000;

                            $currenct_jackpot_text = "";
                            if ($lottery['current_jackpot'] == 0) {
                                            $currenct_jackpot_text = _("Pending");
                            } else {
                                $currenct_jackpot_text = Lotto_View::format_currency(
                                    $currenct_jackpot,
                                    $lottery['currency'],
                                    true
                                );
                            }

                                        $play_info_href = lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']);
                            ?>
                            <tr data-lottery-slug="<?= $lottery['slug'] ?>" data-lottery-type="<?php echo (!empty($lottery['type'])) ? $lottery['type'] : ''; ?>" class="information-lottery-row">
                                <td class="info-image text-nowrap">
                                    <i class="sprite-lang sprite-lang-<?= $country_flag; ?>"></i>
                                                    <?= Security::htmlentities(_($lottery['country'])); ?>
                                </td>
                                <!-- At this moment this column is hidden -->
                                <td class="info-lottery-image">
                                                <img src="<?= UrlHelper::esc_url($lottery_image); ?>" alt="">
                                </td>
                                <td class="info-lottery">
                                                <a href="<?= UrlHelper::esc_url($play_info_href); ?>">
                                                    <?= Security::htmlentities(_($lottery['name'])); ?>
                                    </a>
                                </td>
                                            <td class="info-next" data-type="content-lotteries-next-draw-timestamp"
                                                data-text="<?= htmlspecialchars($last_timestamp); ?>">
                                                <span class="mobile-only-label">
                                                    <?= Security::htmlentities(_("Next Draw")); ?>:
                                                </span>
                                                <span data-type="content-lotteries-next-draw">
                                                    <?= Security::htmlentities($next_date_local); ?>
                                                </span>
                                </td>
                                <td>
                                                <span class="mobile-only-label">
                                                    <?= Security::htmlentities(_("Guess Range")); ?>:
                                                </span> <?= Security::htmlentities($guess_range); ?>
                                </td>
                                <td data-text="<?= htmlspecialchars($odds_formatted) ?>">
                                                <span class="mobile-only-label">
                                                    <?= Security::htmlentities(_("Winning Odds")); ?>:
                                                </span> <?= Security::htmlentities($winning_odds); ?>
                                </td>
                                            <td class="info-jackpot" data-text="<?= htmlspecialchars($currentJackpotUsd); ?>">
                                                <span class="mobile-only-label">
                                                    <?= Security::htmlentities(_("Estimated Jackpot")); ?>:
                                                </span> <span class="info-jackpot-amount" data-type="content-lotteries-jackpot">
                                                    <?= Security::htmlentities($currenct_jackpot_text); ?>
                                                </span>
                                </td>
                                <td class="info-play table-btn text-right">
                                    <a href="<?= UrlHelper::esc_url($lottery_info_href); ?>"
                                       class="btn btn-primary btn-table-first">
                                        <?= _('Information') ?>
                                    </a>
                                    <a href="<?= UrlHelper::esc_url($play_info_href); ?>"
                                       class="btn btn-primary btn-table-second play-button"
                                       data-lottery-slug="<?= $lottery['slug'] ?>"
                                    >
                                        <?= _('Play') ?>
                                    </a>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                        </tbody>
                    </table>
                <?php
                else:
                    ?>
                    <p class="text-info">
                        <?= _('No lotteries.'); ?>
                    </p>
                <?php
                endif;
                ?>
            </div>
            <?php
            /*** end of old widget info area ***/
            ?>
            <section class="page-content page-content-more">
                <article class="page">
                    <?php
                    echo apply_filters('the_content', $post_data['extended']);

                    base_theme_social_share_bottom(
                        $social_share_rows,
                        $counter_socials,
                        $current_url
                    );
                    ?>
                </article>
            </section>
        </div>
    </div>
    <?php
    get_active_sidebar('info-more-sidebar-id');
    ?>
</div>
