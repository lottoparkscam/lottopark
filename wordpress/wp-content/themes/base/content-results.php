<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

$page_posts = apply_filters('wpml_object_id', get_option('page_for_posts'), 'page', false);

$result_more_sidebar = Lotto_Helper::get_widget_main_area_classes(null, "results-more-sidebar-id");

list(
    $social_share_rows,
    $counter_socials,
    $current_url
) = Helpers_General::get_prepared_social_share_links();
?>
<div class="content-area<?= $result_more_sidebar; ?> <?php echo (basename(get_permalink()) === basename(UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("keno-results")))) ? 'page-keno-results-show-only-keno' : null; ?>">
    <?php
        get_content_main_menu($page_posts, 'results');
    
        get_active_sidebar('results-sidebar-id');
    ?>
	<div class="main-width content-width">
            <div class="content-box<?php
                echo Lotto_Helper::get_widget_top_area_classes("results-sidebar-id");
                echo Lotto_Helper::get_widget_bottom_area_classes("results-more-sidebar-id");
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
                    /*** old results widget - moved here ****/

                    $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
                    $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
                ?>
                <div class="results-content">
                    <?php
                        if (!empty($lotteries) && count($lotteries) > 0):
                    ?>
                            <div class="mobile-only pull-right">
                                <label for="results-mobile-sort" class="table-sort-label">
                                    <?= Security::htmlentities(_("Sort by")); ?>: 
                                </label>
                                <select id="results-mobile-sort" class="results-mobile-sort">
                                    <option value="1_0">
                                        <?= Security::htmlentities(_("Lottery")); ?> - <?= Security::htmlentities(_("from A to Z")); ?>
                                    </option>
                                    <option value="1_1">
                                        <?= Security::htmlentities(_("Lottery")); ?> - <?= Security::htmlentities(_("from Z to A")); ?>
                                    </option>
                                    <option value="2_0">
                                        <?= Security::htmlentities(_("Last Draw")); ?> - <?= Security::htmlentities(_("by oldest")); ?>
                                    </option>
                                    <option value="2_1" selected="selected">
                                        <?= Security::htmlentities(_("Last Draw")); ?> - <?= Security::htmlentities(_("by newest")); ?>
                                    </option>
                                    <option value="4_0">
                                        <?= Security::htmlentities(_("Payout")); ?> - <?= Security::htmlentities(_("by lowest")); ?>
                                    </option>
                                    <option value="4_1">
                                        <?= Security::htmlentities(_("Payout")); ?> - <?= Security::htmlentities(_("by highest")); ?>
                                    </option>
                                </select>
                            </div>

                            <div class="clearfix"></div>

                            <table class="table table-results table-hover tablesorter" id="results-table">
                                <thead>
                                    <tr>
                                        <th class="results-lottery-image"></th>
                                        <th><?= Security::htmlentities(_("Lottery")); ?>
                                        </th>
                                        <th>
                                            <?= Security::htmlentities(_("Last Draw")); ?>
                                        </th>
                                        <th>
                                            <?= Security::htmlentities(_("Results")); ?>
                                        </th>
                                        <th>
                                            <?= Security::htmlentities(_("Payout")); ?>
                                        </th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($lotteries['__sort_lastdate'] as $key => $lottery):
                                            $lottery_additional_data = null;
                                            if ($lottery['additional_data']) {
                                                $lottery_additional_data = unserialize($lottery['additional_data']);

                                                if ($lottery_additional_data === false) {
                                                    $lottery_additional_data = null;
                                                }
                                            }
                                            $lottery_image = Lotto_View::get_lottery_image($lottery['id']);
                                            
                                            $lottery_url = lotto_platform_get_permalink_by_slug('results/' . $lottery['slug']);

                                            $last_date_local_UTC  = Lotto_View::get_last_UTC_timestamp($lottery['last_date_local'], $lottery['timezone']);
                                            
                                            $full_last_date_local = $lottery['last_date_local'];
                                            
                                            $last_date_local_formatted = Lotto_View::format_date(
                                                $full_last_date_local,
                                                IntlDateFormatter::MEDIUM,
                                                IntlDateFormatter::NONE,
                                                $lottery['timezone'],
                                                false
                                            );
                                            
                                            $results_row = Lotto_View::format_line(
                                                $lottery['last_numbers'],
                                                $lottery['last_bnumbers'],
                                                null,
                                                null,
                                                null,
                                                $lottery_additional_data
                                            );
                                            
                                            $results_options = array(
                                                "div" => array(
                                                    "class" => array(),
                                                    "data-tooltip" => array()
                                                ),
                                                'span' => array()
                                            );
                                            
                                            $last_jackpot = $lottery['last_total_prize'] * 100;
                                            
                                            $last_jackpot_text = "";
                                            if ($lottery['last_jackpot_prize'] == 0) {
                                                $last_jackpot_text = Security::htmlentities(_("Rollover"));
                                            } else {
                                                $last_jackpot_price = Lotto_View::format_currency(
                                                    $lottery['last_jackpot_prize'],
                                                    $lottery['currency'],
                                                    true
                                                );
                                                $last_jackpot_text = Security::htmlentities($last_jackpot_price);
                                            }
                                            
                                            $last_total_prize = Lotto_View::format_currency(
                                                $lottery['last_total_prize'],
                                                $lottery['currency'],
                                                true
                                            );
                                            
                                            $play_url = lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']);
                                    ?>
                                            <tr class="results-lottery-row" data-lottery-slug="<?= $lottery['slug'] ?>" data-lottery-type="<?php echo (!empty($lottery['type'])) ? $lottery['type'] : ''; ?>">
                                                <!-- At this moment this column is hidden -->
                                                <td class="results-lottery-image">
                                                    <img src="<?= UrlHelper::esc_url($lottery_image); ?>" alt="Lottery">
                                                </td>
                                                <td class="results-lottery">
                                                    <a href="<?php
                                                        echo UrlHelper::esc_url($play_url);
                                                    ?>"><?php
                                                        echo Security::htmlentities(_($lottery['name']));
                                                    ?></a>
                                                    <div class="results-image">
                                                        <i class="sprite-lang sprite-lang-<?php
                                                            echo Lotto_View::map_flags($lottery['country']);
                                                        ?>"></i><?php
                                                            echo Security::htmlentities(_($lottery['country']));
                                                        ?>
                                                    </div>
                                                    
                                                </td>
                                                <td class="results-last" 
                                                    data-text="<?= htmlspecialchars($last_date_local_UTC); ?>">
                                                        <?= Security::htmlentities($last_date_local_formatted); ?>
                                                </td>
                                                <td>
                                                    <?= wp_kses($results_row, $results_options); ?>
                                                </td>
                                                <td class="results-payout" 
                                                    data-text="<?= ($lottery['type'] != 'keno' && !in_array($lottery['slug'], ['gg-world', 'gg-world-x', 'gg-world-million'])) ? htmlspecialchars(intval($lottery['last_total_prize'])) : 0 ; ?>">
                                                    <span class="results-payout-jackpot"><?php
                                                        echo Security::htmlentities(_("Jackpot"));
                                                    ?>: <?= $last_jackpot_text; ?></span>
                                                    <?php if ($lottery['playable']): ?>
                                                        <?php if ($lottery['type'] != 'keno' && !in_array($lottery['slug'], ['gg-world', 'gg-world-x', 'gg-world-million'])): ?><br><?php
                                                            echo Security::htmlentities(_("Total payout"));
                                                        ?>: <br class="mobile-hide"><span class="results-payout-amount"><?php
                                                            echo Security::htmlentities($last_total_prize);
                                                        ?></span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="table-btn">
                                                    <a href="<?php echo UrlHelper::esc_url($lottery_url);?>" 
                                                       class="btn btn-primary btn-table-first">
                                                        <?= _('Results') ?>
                                                    </a>

                                                    <a href="<?= UrlHelper::esc_url($play_url); ?>"
                                                    class="btn btn-primary btn-table-second play-button"
                                                       data-lottery-slug="<?= $lottery['slug'] ?>">
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
                            // TODO: message - no lotteries
                        endif;
                    ?>
                </div>
                <?php
                    /*** end of old results widget ****/
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
        get_active_sidebar('results-more-sidebar-id');
    ?>
</div>
