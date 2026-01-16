<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

?>
<div class="small-widget small-widget-results">
    <div class="small-widget-title">
        <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('results')); ?>">
            <?= empty($title) ? _("Latest results") : $title; ?>
        </a>
    </div>
    <div class="loading-container"><span class="loading loading-big"></span></div>
    <!-- this part will be generated automatically by JS-->
    <script>
        // We set these tags only for first widget. So if there are few results widgets, the first one define this
        // parameters
        if (typeof titleStartTag === 'undefined') {
            window.titleStartTag = '<?= $title_start_tag ?>';
            window.titleEndTag = '<?= $title_end_tag ?>';
            window.lotteryLink = '<?= lotto_platform_get_permalink_by_slug($slug) ?>';
        }
    </script>
</div>