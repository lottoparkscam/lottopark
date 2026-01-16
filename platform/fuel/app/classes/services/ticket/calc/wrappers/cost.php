<?php

use Models\RaffleRule;
use Webmozart\Assert\Assert;
use Models\Raffle;
use Models\WhitelabelRaffleTicket;

/**
 * Class Services_Ticket_Calc_Wrappers_Cost
 * OOP wrapper for Helper::get_price to keep code testable and split bad quality code.
 *
 * todo st: Some discussion what if suppose to be.
 * @see https://ggintsoftware.slack.com/archives/GALAKBCBZ/p1600679025014300?thread_ts=1598958928.131000&cid=GALAKBCBZ
 */
class Services_Ticket_Calc_Wrappers_Cost
{
    private Lotto_Helper $lotto_helper;

    public function __construct(Lotto_Helper $lotto_helper)
    {
        $this->lotto_helper = $lotto_helper;
    }

    /**
     * @param WhitelabelRaffleTicket $ticket
     *
     * @return float
     * @throws Exception
     */
    public function calculate_raffle_cost(WhitelabelRaffleTicket $ticket): float
    {
        $this->verify_ticket($ticket);

        $possible_prizes_sum = $this->calculate_possible_win_amount($ticket->rule);

        $each_line_cost = $possible_prizes_sum / $ticket->rule->max_lines_per_draw;

        switch($ticket->raffle->slug) {
            case Raffle::GG_WORLD_PLATINUM_RAFFLE_SLUG:
                $each_line_cost = 45;
                break;
            case Raffle::GG_WORLD_GOLD_RAFFLE_SLUG:
                $each_line_cost = 9;
                break;
            case Raffle::GG_WORLD_SILVER_RAFFLE_SLUG:
                $each_line_cost = 0.9;
                break;
        }

        return $each_line_cost * count($ticket->lines);
    }

    private function verify_ticket(WhitelabelRaffleTicket $ticket): void
    {
        Assert::notEmpty($ticket->raffle, 'Raffle relation can not be empty');
    }

    private function calculate_possible_win_amount(RaffleRule $rule): float
    {
        $prizes_sum = 0.0;
        foreach ($rule->tiers as $tier) {
            $winners_count = !is_array($tier->matches[0]) ? $tier->matches[0] : count(range($tier->matches[0][0], $tier->matches[0][sizeof($tier->matches[0]) - 1]));
            $prizes_sum += ($tier->prize * $winners_count);
        }
        return $prizes_sum;
    }
}
