<?php
$options = [];

if (!isset($disable_main_configuration)) {
    $formatted_jackpot_text = '';
    $ball_image = '';
    $lottery_name = '';
    
    if (!empty($lottery)) {
        if (!empty($lottery['current_jackpot']) &&
            !empty($lottery['currency'])
        ) {
            $jackpot_text = $lottery['current_jackpot'] * 1000000;
            $formatted_jackpot_text = Lotto_View::format_currency(
                $jackpot_text,
                $lottery['currency'],
                0,
                $translations['lang_code']
            );
        }
        
        if (!empty($lottery['id'])) {
            /*$ball_image = 'wp-content/plugins/lotto-platform/public/images/lotteries/lottery_' .
                $lottery['id'] . '.png';*/
            $ball_image = Lotto_View::get_lottery_image_path($lottery['id']);
        }
        
        if (!empty($lottery['name'])) {
            $lottery_name = $lottery['name'];
        }
    }
    
    $options['main'] = [
        'font' => 'wp-content/themes/base/fonts/SourceSansPro-SemiBold.ttf',
        'fontBold' => 'wp-content/themes/base/fonts/SourceSansPro-Bold.ttf',
        'exceptionFont' => 'wp-content/themes/base/fonts/Amiri/Amiri-Regular.ttf',
        'exceptionFontBold' => 'wp-content/themes/base/fonts/Amiri/Amiri-Bold.ttf',
        'priceText' => $formatted_jackpot_text,
        'starsBG' => 'wp-content/themes/base/images/banners/universal-stars.png',
        'ball' => $ball_image,
        'ballBG' => 'wp-content/themes/base/images/banners/ball-bg.png',
        'wholeBallBG' => 'wp-content/themes/base/images/banners/big-ball-bg.png',
        'lotteryName' => $lottery_name,
        'buttonTitle' => $translations['play_now'],
        'jackpotText' => $translations['nearest_jackpot'],
    ];
}

$options['standard_colors'] = [
    'white' => [
        'borderColor' => 'darkblue',
        'gradient1' => '#FFF',
        'gradient2' => '#FFF',
        'starsColor' => '#bcbcbc',
        'lotteryNameColor' => '#384656',
        'buttonTitleColor' => '#FFF',
        'buttonBGColor' => '#67bd2e',
        'jackpotColor' => '#87979c',
        'priceColor' => '#1db4ed',
    ]
];

$options['el-gordo-primitiva'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#c55266', // Gradient color 1
        'gradient2' => '#aa283f', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['oz-lotto'] = [
        'borderColor' => '#FFF',
        'gradient1' => '#4db236',
        'gradient2' => '#146501',
        'starsColor' => '#FFF',
        'lotteryNameColor' => '#FFF',
        'buttonTitleColor' => '#116507',
        'buttonBGColor' => '#fcdf36',
        'jackpotColor' => '#FFF',
        'priceColor' => '#FFF',
];

$options['monday-wednesday-lotto-au'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#00b9e3', // Gradient color 1
        'gradient2' => '#02509d', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['bonoloto'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#64b253', // Gradient color 1
        'gradient2' => '#146601', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['la-primitiva'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#51a894', // Gradient color 1
        'gradient2' => '#1a6f5b', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['powerball-au'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#8290bd', // Gradient color 1
        'gradient2' => '#3f4a6e', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['saturday-lotto-au'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#b868ae', // Gradient color 1
        'gradient2' => '#961f84', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['euromillions'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#4499d3', // Gradient color 1
        'gradient2' => '#106198', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['eurojackpot'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#c09e00', // Gradient color 1
        'gradient2' => '#7e5b00', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['superenalotto'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#9ac836', // Gradient color 1
        'gradient2' => '#4f6c12', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['powerball'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#f4484d', // Gradient color 1
        'gradient2' => '#a61a18', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['mini-powerball'] = [
    'borderColor' => 'gray', // Border color
    'gradient1' => '#f4484d', // Gradient color 1
    'gradient2' => '#a61a18', // Gradient color 2
    'starsColor' => '#FFF', // color of stars (background)
    'lotteryNameColor' => '#FFF', // Lottery name text color
    'buttonTitleColor' => '#FFF', // Button text color
    'buttonBGColor' => '#67bd2e', // Button background color
    'jackpotColor' => '#FFF', // Jackpot text color
    'priceColor' => '#FFF', // Price text color
];

$options['mega-millions'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#459ad4', // Gradient color 1
        'gradient2' => '#106198', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['lotto-pl'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#5ac5fd', // Gradient color 1
        'gradient2' => '#1298dc', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['lotto-pl'] = [
        'borderColor' => 'gray', // Border color
        'gradient1' => '#5ac5fd', // Gradient color 1
        'gradient2' => '#1298dc', // Gradient color 2
        'starsColor' => '#FFF', // color of stars (background)
        'lotteryNameColor' => '#FFF', // Lottery name text color
        'buttonTitleColor' => '#FFF', // Button text color
        'buttonBGColor' => '#67bd2e', // Button background color
        'jackpotColor' => '#FFF', // Jackpot text color
        'priceColor' => '#FFF', // Price text color
];

$options['lotto-uk'] = [
    'borderColor' => 'gray', // Border color
    'gradient1' => '#f2474c', // Gradient color 1
    'gradient2' => '#a61a18', // Gradient color 2
    'starsColor' => '#FFF', // color of stars (background)
    'lotteryNameColor' => '#FFF', // Lottery name text color
    'buttonTitleColor' => '#FFF', // Button text color
    'buttonBGColor' => '#67bd2e', // Button background color
    'jackpotColor' => '#FFF', // Jackpot text color
    'priceColor' => '#FFF', // Price text color
];

$options['gg-world'] = [
    'borderColor' => 'gray', // Border color
    'gradient1' => '#e19d31', // Gradient color 1
    'gradient2' => '#8d4c15', // Gradient color 2
    'starsColor' => '#FFF', // color of stars (background)
    'lotteryNameColor' => '#FFF', // Lottery name text color
    'buttonTitleColor' => '#FFF', // Button text color
    'buttonBGColor' => '#67bd2e', // Button background color
    'jackpotColor' => '#FFF', // Jackpot text color
    'priceColor' => '#FFF', // Price text color
];

$options['gg-world-x'] = [
    'borderColor' => 'gray', // Border color
    'gradient1' => '#e19d31', // Gradient color 1
    'gradient2' => '#8d4c15', // Gradient color 2
    'starsColor' => '#FFF', // color of stars (background)
    'lotteryNameColor' => '#FFF', // Lottery name text color
    'buttonTitleColor' => '#FFF', // Button text color
    'buttonBGColor' => '#67bd2e', // Button background color
    'jackpotColor' => '#FFF', // Jackpot text color
    'priceColor' => '#FFF', // Price text color
];

$options['gg-world-million'] = [
    'borderColor' => 'gray', // Border color
    'gradient1' => '#e19d31', // Gradient color 1
    'gradient2' => '#8d4c15', // Gradient color 2
    'starsColor' => '#FFF', // color of stars (background)
    'lotteryNameColor' => '#FFF', // Lottery name text color
    'buttonTitleColor' => '#FFF', // Button text color
    'buttonBGColor' => '#67bd2e', // Button background color
    'jackpotColor' => '#FFF', // Jackpot text color
    'priceColor' => '#FFF', // Price text color
];

$options['lotto-zambia'] = [
    'borderColor' => 'gray', // Border color
    'gradient1' => '#1d9a45', // Gradient color 1
    'gradient2' => '#017627', // Gradient color 2
    'starsColor' => '#FFF', // color of stars (background)
    'lotteryNameColor' => '#FFF', // Lottery name text color
    'buttonTitleColor' => '#FFF', // Button text color
    'buttonBGColor' => '#67bd2e', // Button background color
    'jackpotColor' => '#FFF', // Jackpot text color
    'priceColor' => '#FFF', // Price text color
];

$options['somoslotto-plus'] = [
    'borderColor' => 'gray', // Border color
    'gradient1' => '#8e589d', // Gradient color 1
    'gradient2' => '#572fac', // Gradient color 2
    'starsColor' => '#FFF', // color of stars (background)
    'lotteryNameColor' => '#FFF', // Lottery name text color
    'buttonTitleColor' => '#FFF', // Button text color
    'buttonBGColor' => '#67bd2e', // Button background color
    'jackpotColor' => '#FFF', // Jackpot text color
    'priceColor' => '#FFF', // Price text color
];

$options['gg-world-keno'] = [
    'borderColor' => 'gray', // Border color
    'gradient1' => '#8e589d', // Gradient color 1
    'gradient2' => '#572fac', // Gradient color 2
    'starsColor' => '#FFF', // color of stars (background)
    'lotteryNameColor' => '#FFF', // Lottery name text color
    'buttonTitleColor' => '#FFF', // Button text color
    'buttonBGColor' => '#67bd2e', // Button background color
    'jackpotColor' => '#FFF', // Jackpot text color
    'priceColor' => '#FFF', // Price text color
];

$options['polish-keno'] = [
    'borderColor' => 'gray', // Border color
    'gradient1' => '#8e589d', // Gradient color 1
    'gradient2' => '#572fac', // Gradient color 2
    'starsColor' => '#fff', // color of stars (background)
    'lotteryNameColor' => '#fff', // Lottery name text color
    'buttonTitleColor' => '#fff', // Button text color
    'buttonBGColor' => '#67bd2e', // Button background color
    'jackpotColor' => '#fff', // Jackpot text color
    'priceColor' => '#fff', // Price text color
];

$options['greek-keno'] = [
    'borderColor' => 'gray', // Border color
    'gradient1' => '#8e589d', // Gradient color 1
    'gradient2' => '#572fac', // Gradient color 2
    'starsColor' => '#fff', // color of stars (background)
    'lotteryNameColor' => '#fff', // Lottery name text color
    'buttonTitleColor' => '#fff', // Button text color
    'buttonBGColor' => '#67bd2e', // Button background color
    'jackpotColor' => '#fff', // Jackpot text color
    'priceColor' => '#fff', // Price text color
];

$options['czech-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#8e589d',
    'gradient2' => '#572fac',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['slovak-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#8e589d',
    'gradient2' => '#572fac',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['latvian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#8e589d',
    'gradient2' => '#572fac',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['finnish-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#8e589d',
    'gradient2' => '#572fac',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['french-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#8e589d',
    'gradient2' => '#572fac',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['eurodreams'] = [
    'borderColor' => 'gray',
    'gradient1' => '#8e589d',
    'gradient2' => '#572fac',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['hungarian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#8e589d',
    'gradient2' => '#572fac',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['italian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#8e589d',
    'gradient2' => '#572fac',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['weekday-windfall'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['slovak-keno-10'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['german-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['ukrainian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['belgian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['keno-ny'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['euromillions-superdraw'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['loto-6-49'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['brazilian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['swedish-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['australian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['danish-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['norwegian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['lithuanian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['croatian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['belarusian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['estonian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['canadian-keno'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['mini-mega-millions'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['mini-euromillions'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['mini-eurojackpot'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];

$options['mini-superenalotto'] = [
    'borderColor' => 'gray',
    'gradient1' => '#00b9e3',
    'gradient2' => '#02509d',
    'starsColor' => '#fff',
    'lotteryNameColor' => '#fff',
    'buttonTitleColor' => '#fff',
    'buttonBGColor' => '#67bd2e',
    'jackpotColor' => '#fff',
    'priceColor' => '#fff',
];
