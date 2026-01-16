<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;

/**
 * This seeder creates lottery pages with category.
 */

final class KenoPageLottery extends AbstractPageLottery
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const CATEGORY_NAME = 'GG World Keno';
    protected const GAME_NAME_SLUG = 'gg-world-keno';
    protected const IS_PARENT = false;

    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'results' => [
                'title' => 'GG World Keno Results',
                'body' => '<p>Check the latest results of <strong>GG World Keno</strong> draw 
                here to see if you have been lucky enough to claim one of the prizes – 
                or maybe you have picked all the required numbers correctly and <strong>won the great jackpot</strong>!
                 You can also check the results of past draws by selecting a date and time from the dropbox above.</p>
                 <p>Check the table above to see the information about prizes.</p>',
            ],
            'play' => [
                'title' => 'Play GG World Keno',
                'body' => '<h2>How to play GG World Keno</h2>
                <p>Fast-paced, fun, engaging, and awarding, <strong>GG World Keno</strong> 
                brings you the chance of winning amazing prizes <strong>every 4 minutes</strong>. 
                Pick 1-10 numbers, select a multiplier, and check the 
                <a href="https://[whitelabelDomain]/results/gg-world-keno/">results</a> 
                to see if you have won a prize. 
                You can pick the numbers yourself or use the quick-pick tool to fill in the ticket with random numbers. 
                Make sure to visit the <a href="https://[whitelabelDomain]/lotteries/gg-world-keno/">rules page</a> for more detailed information about the game.</p>'
            ],
            'lotteries' => [
                'title' => 'GG World Keno Information',
                'body' => '<p>The player picks 1-10 numbers within 1-70 range. 
                You can pick the numbers manually or use the quick-pick tool which will pick the numbers for you at random. 
                The draw takes place <strong>every 4 minutes</strong>. 
                If the numbers drawn match the numbers you picked, you win!</p>
                <p>The basic <strong>GG World Keno</strong> stake is 1x for a single line. 
                You can choose up to 10 lines on a ticket.</p><p>You can set a 
                <strong>multiplier </strong>of up to 10x to increase the prizes.
                 Use the dropdown selector and the table below to see how the selected multiplier affects the prizes.</p>',
            ]
        ],
        'pl' => [
            'results' => [
                'title' => 'Wyniki losowań GG World Keno',
                'body' => '',
            ],
            'play' => [
                'title' => 'Graj w GG World Keno',
                'body' => '',
            ],
            'lotteries' => [
                'title' => 'Informacje o GG World Keno',
                'body' => '',
            ]
        ]
    ];
}
