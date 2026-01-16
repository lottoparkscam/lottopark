<?php

use Models\RaffleDraw;
use Models\RafflePrize;
use Orm\RecordNotFound;
use Models\WhitelabelRaffleTicket;

/**
 * Class Services_Raffle_Sync_Ticket_Updater
 */
class Services_Raffle_Sync_Ticket_Updater
{
    private RaffleDraw $draw_dao;
    private RafflePrize $prize_dao;
    private Services_Raffle_Sync_Ticket_Validator $validator;
    private Services_Ticket_Calc_Prize $prize_calc;

    public function __construct(
        Services_Raffle_Sync_Ticket_Validator $validator,
        RafflePrize $prize,
        Services_Ticket_Calc_Prize $prize_calc,
        RaffleDraw $draw
    ) {
        $this->draw_dao = $draw;
        $this->prize_dao = $prize;
        $this->validator = $validator;
        $this->prize_calc = $prize_calc;
    }

    public function update_ticket_by_lcs_data(
        WhitelabelRaffleTicket $ticket,
        array $lcs_ticket_data,
        Closure $pre_update_handler
    ): void {
        $this->validator->validate($lcs_ticket_data);

        $ticket->status = $lcs_ticket_data['status'];
        $ticket->draw_date = $lcs_ticket_data['draw_date'];
        $ticket->prize_local = $lcs_ticket_data['prize'];
        $ticket->draw = $this->get_draw_by_date($ticket->raffle->id, $lcs_ticket_data['draw_date']);
        $ticket->raffle_draw_id = $ticket->draw->id; # todo: it's weird by something wrong with fuel ORM's

        $this->update_lines($ticket, $lcs_ticket_data['lottery_ticket_lines']);

        $this->prize_calc->calculate($ticket);

        call_user_func($pre_update_handler, $ticket);
    }

    private function get_draw_by_date(int $raffle_id, string $draw_date): RaffleDraw
    {
        $draws = $this->draw_dao->find_draws_by_date($raffle_id, $draw_date);
        if (empty($draws)) {
            throw new RecordNotFound(sprintf('Unable to find Draw for Raffle <%d> from date <%s>. Maybe you forgot to sync draws by task firstly?', $raffle_id, $draw_date));
        }
        if (sizeof($draws) !== 1) {
            throw new InvalidArgumentException(sprintf('There was more Draw results for Raffle <%d> from date <%s> than 1. It should never happened.', $raffle_id, $draw_date));
        }
        return reset($draws);
    }

    private function update_lines(WhitelabelRaffleTicket $ticket, array $lottery_ticket_lines): void
    {
        if (sizeof($ticket->lines) !== count($lottery_ticket_lines)) {
            throw new InvalidArgumentException('Ticket lines count is not equal to LCS fetched data');
        }

        $find_line_by_number = function (int $number) use (&$lottery_ticket_lines): array {
            $results = array_filter($lottery_ticket_lines, function (array $line_data) use (&$number) {
                return $line_data['numbers'][0][0] === $number;
            });
            return reset($results);
        };

        foreach ($ticket->lines as $line) {
            $lcs_line_data = $find_line_by_number($line->number);
            $line->status = $lcs_line_data['status'];

            if ($line->status === Helpers_General::TICKET_STATUS_WIN) {
                $line->raffle_prize = $this->get_prize_by_tier_slug(
                    $ticket->draw->id,
                    $lcs_line_data['lottery_prize']['lottery_rule_tier']['slug'],
                    $ticket->raffle_rule_id
                );
            }
        }
    }

    private function get_prize_by_tier_slug(int $draw_id, string $tier_slug, int $raffle_rule_id): RafflePrize
    {
        try {
            return $this->prize_dao->get_prize_by_tier_slug($draw_id, $tier_slug, $raffle_rule_id);
        } catch (RecordNotFound $exception) {
            throw new RecordNotFound(sprintf('Unable to find prize for tier <%s> from draw <%d>', $tier_slug, $draw_id));
        }
    }
}
