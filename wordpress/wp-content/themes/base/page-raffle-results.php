<?php

if (!defined('WPINC')) {
    die;
}

get_header();
get_template_part('content', 'login-register-box-mobile');

$lotteryName = $post->post_name;
$locale = explode('_', get_locale());
$language = $locale[0];
?>
    <div id="content-results-lottery">
        <?= get_template_part('content', 'raffle-results') ?>
    </div>
    <script>
      window.lotteryName = `<?= htmlspecialchars($lotteryName) ?>`;
      window.lotteryLanguage = `<?= $language ?>`;
    </script>
<?php
get_footer();
