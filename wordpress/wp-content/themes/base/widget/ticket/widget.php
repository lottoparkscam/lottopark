<?php

use Services\Logs\FileLoggerService;

if (!defined('WPINC')) {
    die;
}

$fileLoggerService = Container::get(FileLoggerService::class);

$lottery_type_nrange = -1;
$lottery_type_ncount = -1;
$lottery_type_brange = -1;
$lottery_type_bcount = -1;

if (isset($lottery_type) && is_array($lottery_type)) {
    $lottery_type_nrange = intval($lottery_type['nrange']);
    $lottery_type_ncount = intval($lottery_type['ncount']);
    $lottery_type_brange = intval($lottery_type['brange']);
    $lottery_type_bcount = intval($lottery_type['bcount']);
}

$now = new DateTime("now", new DateTimeZone("UTC"));

$lottery_id = -1;
$lottery_min_bets = -1;
$lottery_max_bets = -1;

$pricing = "0.00";
$minimum_lines = 1;
$lottery_multiplier = 0;
$lottery_current_jackpot = 1;
$lottery_currency = Helpers_Currency::get_default_currency_code();
$towin = '-';
$lottery_next_date_utc = '1970-01-01';
$before_next_date_utc = false;
$next_draw_timestamp = -1;
$draw_in_human_time_escaped = '';
$lottery_image = '';
$lottery_name = '';

$lottery_slug = 'nolottery';

$pick_numbers_text = '';

// It is used only in keno, but it doesn't matter
// Keno is every 4 minutes so timezone has no matter
// And nextDrawTime is changed dynamically, we use it only to display it for robots
$userTimezone = get_user_timezone();

$user = Lotto_Settings::getInstance()->get("user");

if (isset($lottery) && is_array($lottery)) {
    $lottery_id = intval($lottery['id']);

    $lottery_min_bets = intval($lottery['min_bets']);
    $lottery_max_bets = intval($lottery['max_bets']);

    $minimum_lines = $lottery['min_lines'] > 0 ? $lottery['min_lines'] : 1;
    $lottery_multiplier = $lottery['multiplier'];

    $lottery_current_jackpot = $lottery['current_jackpot'];
    $lottery_currency = $lottery['currency'];

    if (empty($lottery_current_jackpot) && $lottery['type'] === 'keno' & isset($lottery_type) && is_array($lottery_type)) {
        try {
            $lotteryTypesData = Model_Lottery_Type_Data::find_by_lottery_type_id_and_slug($lottery_type['id'], 'keno-10-10');
            $lotteryMinMaxMultiplier = Model_Lottery_Type_Multiplier::min_max_for_lottery($lottery_id);

            if (!empty($lotteryTypesData[0]->prize)) {
                $kenoMaxPrize = $lotteryTypesData[0]->prize * $lotteryMinMaxMultiplier['max'];
            }
            if (!empty($kenoMaxPrize)) {
                $kenoMaxPrize = Lotto_View::getKenoMaxPrizeConvertedToText(
                    $kenoMaxPrize,
                    $lottery_currency,
                    $lottery['force_currency']
                );
            }
        } catch (Exception $exception) {
            $fileLoggerService->error(
                "Error occurred when trying to get kenoMaxPrize for lottery_type['id']: " . $lottery_type['id'] . " and lottery_id: " . $lottery_id
            );
        }

    }

    list(
        $towin,
        $formatted_thousands
        ) = Lotto_View::get_jackpot_formatted_to_text(
            $lottery_current_jackpot,
            $lottery_currency,
            Helpers_General::SOURCE_WORDPRESS,
            $lottery['force_currency']
        );

    $kenoMaxPrize = $kenoMaxPrize ?? $towin;

    $lottery_next_date_utc = $lottery['next_date_utc'];

    $ndd = DateTime::createFromFormat(
        "Y-m-d H:i:s",
        $lottery_next_date_utc,
        new DateTimeZone("UTC")
    );

    if ($now < $ndd) {
        $before_next_date_utc = true;
    }

    $next_draw = Lotto_Helper::get_lottery_real_next_draw($lottery);
    $next_draw->setTimezone(Lotto_Settings::getInstance()->get("timezone") ?? 'UTC');
    $next_draw_timestamp = $next_draw->getTimestamp();

    $draw_in_human_time = sprintf(
        _("draw in %s"),
        human_time_diff($next_draw_timestamp, $now->getTimestamp())
    );
    $draw_in_human_time_escaped = Security::htmlentities($draw_in_human_time);

    $lottery_image = Lotto_View::get_lottery_image($lottery_id);

    $lottery_name = $lottery['name'];
    $lottery_slug = $lottery['slug'];

    if ($lottery_type_bcount > 0) {
        $pick_numbers_text = Security::htmlentities(sprintf(_("Pick %d numbers"), $lottery_type_ncount) . ' ' .
            sprintf(Lotto_Helper::get_lottery_bonus_ball_name($lottery), $lottery_type_bcount));
    } else {
        $pick_numbers_text = Security::htmlentities(sprintf(_("Pick %d numbers"), $lottery_type_ncount));
    }

    $group_lotteries = null;
    if (!empty($lottery['group_id'])) {
        $group_lotteries = Lotto_Helper::get_grouped_lotteries($lottery['group_id']);
    }

    $viewItemData = [
        'event' => 'view_item',
        'user_id' =>  $user ? $whitelabel['prefix'] . 'U' . $user['token'] : '',
        'items' => [
            'item_id' => $lottery_slug,
            'item_name' => $lottery_name,
        ],
    ];

    include(__DIR__ . DIRECTORY_SEPARATOR . $widget_extension_file);
}
