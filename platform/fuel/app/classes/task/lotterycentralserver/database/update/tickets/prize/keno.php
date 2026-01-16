<?php


final class Task_Lotterycentralserver_Database_Update_Tickets_Prize_Keno extends Task_Lotterycentralserver_Database_Update_Tickets_Prize
{
    protected function push_prize_type_id($prize, array &$type_ids): void
    {
        $type_ids[$prize['slug']] = $prize['lottery_type_data_id'];
        if ($prize['is_jackpot']) {
            $this->jackpot_prize = $prize['slug'];
        };
    }

    protected function prepare_winning_line($line, &$lcs_line, &$winning_tickets, &$winning_lines_ids_grouped, $last_ticket_id, &$prizes)
    {
        $type_ids = $this->type_ids ?: $this->type_ids = $this->get_type_ids();
        $prize = $lcs_line['lottery_prize']['lottery_rule_tier']['slug'];
        $manager_currency_id = &$winning_tickets[$last_ticket_id]['manager_currency_id'];
        $user_currency_id = &$winning_tickets[$last_ticket_id]['user_currency_id'];
        $winning_lines_ids_grouped[$prize]['value'] = $lcs_line['lottery_prize']['per_user'];
        $winning_lines_ids_grouped[$prize]['payout'] = $this->should_payout($lcs_line['lottery_prize']['per_user']);
        $winning_lines_ids_grouped[$prize]['type_id'] = $type_ids[$prize]; // collect type id
        $winning_lines_ids_grouped[$prize]['manager_currency_ids'][$manager_currency_id][$user_currency_id][] = $line['id'];
        $prizes[$prize] = $lcs_line['lottery_prize']['per_user']; // collect line prize
    }
}