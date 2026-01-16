<?php

use Carbon\Carbon;

if (!defined('WPINC')) {
    die;
}

get_header();
get_template_part('content', 'login-register-box-mobile');

$lotteryName = $post->post_name;
$locale = explode('_', get_locale());
$language = $locale[0];

if ($post->post_parent !== 0) {
    $lottery = lotto_platform_get_lottery_by_slug($post->post_name);
    $lotteryDraws = empty($lottery) ? [] : Model_Lottery_Draw::get_draw_list_by_lottery($lottery);
    $date = empty($lottery) || empty($lotteryDraws) ?
        null :
        Carbon::parse(
                $lotteryDraws[0]['date_local'],
                $lottery['timezone']
        )->format(Helpers_Time::DATETIME_FORMAT);

    $nextDrawKey = count($lotteryDraws) > 1 ? 1 : null;
    Lotto_Settings::getInstance()->set('results_prev', null);
    Lotto_Settings::getInstance()->set('results_next', $nextDrawKey);
    Lotto_Settings::getInstance()->set('results_date', $date);
?>
    <div id="content-results-lottery">
        <?=
            get_template_part('content', 'results-lottery', [
                    'post' => $post,
                    'date' => $date,
                    'lottery_draws' => $lotteryDraws
            ])
        ?>
    </div>
    <script>
        window.lotteryName = `<?= htmlspecialchars($lotteryName) ?>`;
        window.lotteryLanguage = `<?= $language ?>`;
        window.isLotteryResultsPage = true;
    </script>
<?php
} else {
    get_template_part('content', 'results');
}

get_footer();
