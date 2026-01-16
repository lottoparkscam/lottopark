<?php

use Models\Raffle;
use Webmozart\Assert\Assert;

class Services_Raffle_Number_Validator
{
    /**
     * @param Raffle $raffle
     * @param array|string[]|int[] $ticket_numbers
     * @return array|int[]
     */
    public function validate(Raffle $raffle, array $ticket_numbers): array
    {
        Assert::greaterThan(count($ticket_numbers), 0, 'At least one ticket number is required');
        Assert::lessThanEq(count($ticket_numbers), $raffle->max_bets, sprintf('Too much bets provided. Lottery accept max %d numbers, %d given.', $raffle->max_bets, count($ticket_numbers)));
        Assert::uniqueValues($ticket_numbers, 'Some of numbers are duplicated.');
        [$min, $max] = $raffle->getFirstRule()->ranges[0];
        return array_map(function ($number) use (&$min, &$max) {
            Assert::numeric($number, sprintf('Value %s is not valid number', $number));
            Assert::range($number, $min, $max, sprintf('Number must be in range %d - %d', $min, $max));
            return (int)$number;
        }, $ticket_numbers);
    }
}
