<?php

use Helpers\NumberHelper;

/**
 * Update user balance, based on paid out tickets.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-02
 * Time: 12:07:45
 */
final class Task_Lotterycentralserver_Database_Update_Users_Balance extends Task_Lotterycentralserver_Database_Task
{

    /**
     * Filter pay out prizes to form ready for database use.
     * Grouped over prizes - that number of updates will be minimized.
     *
     * @return array filtered data
     */
    private function filter_pay_out_prizes(): array
    {
        // NOTE: while filtering we will calculate to user currency and transform by prize_payout_percent. 
        $filtered_pay_out_prizes = [];
        foreach ($this->previous_task_result->get_data_item('pay_out_prizes') as $user => $user_prize) {
            $prize_payout_percent = &$user_prize['prize_payout_percent'];
            $user_currency_code = &$user_prize['currency'];
            $lottery_currency = &$user_prize['lottery_currency'];
            $user_prize_value = 0;
            foreach ($user_prize['prizes_by_tickets'] as $whitelotto_ticket_id => $ticket_prize) {
                $prize_to_pay = Helpers_Currency::get_recalculated_to_given_currency($ticket_prize, $lottery_currency, $user_currency_code);
                $user_prize_value += NumberHelper::round_currency($prize_to_pay * $prize_payout_percent / 100);
            }
            $filtered_pay_out_prizes[(string)$user_prize_value][] = $user; // NOTE: we have to cast float to string - key can be only string or int, otherwise it will cast to int.
        }
        return $filtered_pay_out_prizes;
    }

    public function run(): void
    {
        // first we need to filter pay_out_prizes to form appropriate for update
        $filtered_pay_out_prizes = $this->filter_pay_out_prizes();

        // update over prizes (number of users should have balance increased over specified prize)
        foreach ($filtered_pay_out_prizes as $prize => $ids) {
            Model_Whitelabel_User::update_balance($ids, $prize); // TODO: {Vordis 2019-07-03 10:36:13} maybe additional squash to multiquery (; separated)
        }

        // set result item for last task in chain
        $this->get_result()->put_data_item(
            'pay_out_uuids',
            $this->previous_task_result->get_data_item('pay_out_uuids')
        );
    }
}
