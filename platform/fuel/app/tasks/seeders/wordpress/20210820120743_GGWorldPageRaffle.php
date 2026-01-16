<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;

final class GGWorldPageRaffle extends AbstractPageRaffle
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const IS_PARENT = false;
    protected const GAME_NAME_SLUG = 'gg-world-raffle';
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'results-raffle' => [
                'title' => 'GG World Raffle Results',
                'body' => '
                <p>Check the latest draw results of GG World Raffle and compare them with your ticket number.
                First, check if your ticket number (or numbers, in case you’ve participated in 
                the draw with more than one ticket) matches the one displayed under the first 
                tier winner. Click „Show tickets” on the right to see if you have become the 
                great winner of the main prize.</p>
                <p>We are aware that the past draw results are also important for the players.
                You can check all the archive draw results by selecting the draw date from the dropbox.
                This feature is highly useful in case you’ve missed a draw and the next one already 
                took place or you’d like to prepare your playing strategy by analyzing the past 
                draw results to find the ticket numbers that have been drawn the most or the least often.
                </p>
                <p>Don’t waste your time and buy GG World Raffle tickets online now!
                We wish you good luck! Have fun!</p>',
            ],
            'play-raffle' => [
                'title' => 'Play GG World Raffle',
                'body' => '',
            ],
            'information-raffle' => [
                'title' => 'GG World Raffle Information',
                'body' => '
                <p>GG World Raffle is the newest addition to the GG World games family.
                [whitelabelName] bring you the possibility to participate in this unique game and win.
                What makes GG World Raffle so special? First of all, raffle model is pretty different 
                from the traditional lotteries you perfectly know like Powerball, Mega Millions 
                or Eurojackpot just to name a few.</p>
                <p>Participating in a raffle draw, you don’t pick a set of numbers. 
                Instead, you select a ticket number (or numbers if you decide to buy more tickets and
                increase your winning chances). The draw doesn’t select a winning numbers’ set, but the 
                winning tickets. Why is that so important? Being a lottery player, you perfectly know 
                that jackpots can remain unclaimed for weeks or even months, and the draws pass without a winner.</p>
                <h2>GG World Raffle Prizes And Winners</h2>
                <p>That’s not the case with raffle. Each and every draw always brings a winner of the main
                prize! No more rollovers, guaranteed winner after the draw. That makes GG World Raffle 
                so attractive for the players. The main prize isn’t the only one waiting to be claimed,
                there are also second-tier prizes waiting. Don’t hesitate, navigate to Play page, pick
                your ticket and become a winner of GG World Raffle now!</p>',
            ],
            'purchase' => [
                'body' => '',
            ],
        ],
        'pl' => [
            'results-raffle' => [
                'title' => 'Wyniki GG World Raffle',
                'body' => '',
            ],
            'play-raffle' => [
                'title' => 'Graj w GG World Raffle',
                'body' => '',
            ],
            'information-raffle' => [
                'title' => 'Informacje o GG World Raffle',
                'body' => '',
            ],
            'purchase' => [
                'body' => '',
            ],
        ],
    ];
}
