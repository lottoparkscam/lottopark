<?php
    use Helpers\UrlHelper;

    $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
    $showCategories = (int)$whitelabel['show_categories'];

    if (!empty($lottery) && is_array($lottery)):
        $term = get_term_by_lottery_slug($lottery['slug'], $category);
        
        $play_lottery_value = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']));
        $play_lottery_text = Security::htmlentities(sprintf(_("Play %s"), _($lottery['name'])));
        
        $results_lottery_value = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('results/' . $lottery['slug']));
        $results_lottery_text = Security::htmlentities(sprintf(_("%s Results"), _($lottery['name'])));
        
        $information_lottery_value = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('lotteries/' . $lottery['slug']));
        $information_lottery_text = Security::htmlentities(sprintf(_("%s Information"), _($lottery['name'])));
        
        if (!empty($term) && !is_wp_error($term)) {
            $news_lottery_value = UrlHelper::esc_url(get_term_link($term, 'category'));
            $news_lottery_text = Security::htmlentities(sprintf(_("%s News"), _($lottery['name'])));
        }
        
        // This should not be shown on News page
        if ($show_main_width_div):
?>
            <div class="main-width <?= $relative_class_value; ?>">
<?php
        endif;
?>
                <div class="content-nav-wrapper mobile-only">
                    <select class="content-nav">
                        <option <?php if ($selected_class_values[0]): ?>selected<?php endif; ?> 
                                value="<?= $play_lottery_value; ?>">
                            <?= $play_lottery_text; ?>
                        </option>
                        <option <?php if ($selected_class_values[1]): ?>selected<?php endif; ?> 
                                value="<?= $results_lottery_value; ?>">
                            <?= $results_lottery_text; ?>
                        </option>
                        <option <?php if ($selected_class_values[2]): ?>selected<?php endif; ?> 
                                value="<?= $information_lottery_value; ?>">
                            <?= $information_lottery_text; ?>
                        </option>
                        <?php
                            if ($showCategories && !empty($term) && !is_wp_error($term)):
                        ?>
                                <option <?php if ($selected_class_values[3]): ?>selected<?php endif; ?> 
                                        value="<?= $news_lottery_value; ?>">
                                    <?= $news_lottery_text; ?>
                                </option>
                        <?php
                            endif;
                        ?>
                    </select>
                </div>
                <nav class="content-nav mobile-hide">
                    <ul>
                        <li <?php if ($selected_class_values[0]): ?>class="content-nav-active"<?php endif; ?>>
                            <a href="<?= UrlHelper::esc_url($play_lottery_value) ?>">
                                <?= $play_lottery_text; ?>
                            </a>
                        </li>
                        <li <?php if ($selected_class_values[1]): ?>class="content-nav-active"<?php endif; ?>>
                            <a href="<?= UrlHelper::esc_url($results_lottery_value) ?>">
                                <?= $results_lottery_text; ?>
                            </a>
                        </li>
                        <li <?php if ($selected_class_values[2]): ?>class="content-nav-active"<?php endif; ?>>
                            <a href="<?= UrlHelper::esc_url($information_lottery_value) ?>">
                                <?= $information_lottery_text; ?>
                            </a>
                        </li>
                        <?php
                            if ($showCategories && !empty($term) && !is_wp_error($term)):
                        ?>
                                <li <?php if ($selected_class_values[3]): ?>class="content-nav-active"<?php endif; ?>>
                                    <a href="<?= UrlHelper::esc_url($news_lottery_value) ?>">
                                        <?= $news_lottery_text; ?>
                                    </a>
                                </li>
                        <?php
                            endif;
                        ?>
                        <div class="clearfix"></div>
                    </ul>
                </nav>
<?php
        // This should not be shown on News page
        if ($show_main_width_div):
?>
            </div>
<?php
        endif;
    endif;
