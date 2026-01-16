<?php

use Helpers\UrlHelper;

$term = get_term_by_lottery_slug($raffle['slug'], $category);

const PLAY_RAFFLE_TYPE = 'play';
const RAFFLE_RESULTS_TYPE = 'results';
const RAFFLE_INFORMATION_TYPE = 'information';
const LINK_TYPES = [PLAY_RAFFLE_TYPE, RAFFLE_RESULTS_TYPE, RAFFLE_INFORMATION_TYPE];

$get_raffle_link_text = function (string $type) use (&$raffle): string {
    $raffle_name = $raffle['name'];
    $type_translation = _(ucfirst($type));
    # small conversion to make 'human readable' translation, for example: Play Raffle, Raffle Information etc
    $sentence = $type === PLAY_RAFFLE_TYPE ? "$type_translation $raffle_name" : "$raffle_name $type_translation";
    return Security::htmlentities($sentence);
};

function is_active(string $url): bool
{
    $current_url = $_SERVER['REQUEST_URI'];
    $current_url_without_language = $current_url;

    $locale = explode('_', get_locale());
    $language = $locale[0];

    if (strpos($current_url, '/' . $language) !== false) {
        $current_url_without_language = substr($current_url, 3);
    }

    return strpos($url, $current_url) !== false || strpos($url, $current_url_without_language) !== false;
}

$get_raffle_link_url = function (string $type) use (&$raffle): string {
    $parent_slug = lotto_platform_get_permalink_by_slug($type . '-raffle');
    return UrlHelper::esc_url(lotto_platform_get_permalink_by_slug($parent_slug . $raffle['slug']));
};

if (!empty($term) && !is_wp_error($term)) {
    $news_lottery_value = UrlHelper::esc_url(get_term_link($term, 'category'));
    $news_lottery_text = Security::htmlentities(sprintf(_("%s News"), _($raffle['name'])));
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
            <?php foreach (LINK_TYPES as $type): ?>
                <option <?php if (is_active($get_raffle_link_url($type))): ?>selected<?php endif; ?>
                        value="<?= $get_raffle_link_url($type) ?>">
                    <?= $get_raffle_link_text($type) ?>
                </option>
            <?php endforeach; ?>
            <?php
            if (!empty($term) && !is_wp_error($term)):
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
            <?php foreach (LINK_TYPES as $type): ?>
            <li <?php if (is_active($get_raffle_link_url($type))): ?>class="content-nav-active"<?php endif; ?>>
                <a href="<?= $get_raffle_link_url($type) ?>">
                    <?= $get_raffle_link_text($type) ?>
                </a>
            </li>
            <?php endforeach; ?>
            <?php
            if (!empty($term) && !is_wp_error($term)):
                ?>
                <li <?php if ($selected_class_values[3]): ?>class="content-nav-active"<?php endif; ?>>
                    <a href="<?= $news_lottery_value; ?>">
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
