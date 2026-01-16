<?php

use Fuel\Core\Database_Query_Builder_Update;
use Fuel\Core\DB;
use Helpers\NumberHelper;

/**
 * Update tickets prize data.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-25
 * Time: 13:43:12
 */
class Task_Lotterycentralserver_Database_Update_Tickets_Prize extends Task_Lotterycentralserver_Database_Task
{

    /**
     * Tickets with uuids.
     *
     * @var array
     */
    protected $ticket_with_uuid_and_currencies;

    /**
     * Prizes for current draw.
     *
     * @var Model_Lottery_Prize_Data[]
     */
    protected $prizes;

    /**
     * All currencies in system, needed to properly calculate different prizes fields.
     *
     * @var array
     */
    protected $currencies;

    /**
     * Lottery model
     *
     * @var Model_Lottery
     */
    protected $lottery;

    /**
     * Provider model, needed to properly calculate _net prizes.
     *
     * @var Model_Lottery_Provider
     */
    protected $lottery_provider;

    /**
     * Lottery currency id needed to calculate prize_usd.
     *
     * @var int
     */
    protected $lottery_currency_id;

    /**
     * Jackpot prize value.
     *
     * @var string|null
     */
    protected $jackpot_prize = null;

    /**
     * key: prize_payout_percent, value: user_ids .
     *
     * @var int[][]
     */
    protected $user_ids_grouped_by_payout_percent = [];

    /**
     * key: prize_payout_percent, value: user_ids .
     *
     * @var array
     */
    protected $type_ids;


    /**
     * Create new update tickets prize task.
     *
     * @param Task_Result                $previous_task_result            result of task fetching tickets from lcs.
     * @param array                      $ticket_with_uuid_and_currencies Tickets with uuids.
     * @param Model_Lottery_Prize_Data[] $prizes                          Prizes for current draw.
     * @param array                      $currency_ids                    All currencies in system, needed to properly
     *                                                                    calculate different prizes fields.
     * @param Model_Lottery_Provider     $lottery_provider                Provider model, needed to properly calculate
     *                                                                    _net prizes.
     * @param int                        $lottery_currency_id             Lottery currency id needed to calculate
     *                                                                    prize_usd.
     * @param Model_Lottery              $lottery
     */
    protected function __construct(Task_Result $previous_task_result, array $ticket_with_uuid_and_currencies, array $prizes, array $currency_ids, Model_Lottery_Provider $lottery_provider, int $lottery_currency_id, Model_Lottery $lottery)
    {
        parent::__construct($previous_task_result);
        $this->ticket_with_uuid_and_currencies = $ticket_with_uuid_and_currencies;
        $this->prizes = $prizes;
        $this->currencies = $currency_ids;
        $this->lottery_provider = $lottery_provider;
        $this->lottery_currency_id = $lottery_currency_id;
        $this->lottery = $lottery;
    }

    /**
     * Check if current prize should be payout.
     *
     * @param float $prize_local
     *
     * @return boolean
     */
    protected function should_payout(float $prize_local): bool
    {
        return $prize_local <= $this->lottery_provider->max_payout;
    }

    private function clear_for_winning_existent_ticket(array &$ticket_id_to_prize_map, array &$winning_ticket_ids_grouped, int $whitelotto_ticket_id, int $manager_currency_id, int $user_currency_id, string $prize): void
    {
        $old_prize = NumberHelper::round_currency($ticket_id_to_prize_map[$whitelotto_ticket_id] - $prize);
        unset($winning_ticket_ids_grouped[$old_prize]['manager_currency_ids'][$manager_currency_id][$user_currency_id][$whitelotto_ticket_id]);
        // cascade if this was the only entry
        // TODO: {Vordis 2019-11-13 13:49:54} done fast way, could be abstracted
        if (!empty($winning_ticket_ids_grouped[$old_prize]['manager_currency_ids'][$manager_currency_id][$user_currency_id])) {
            return;
        }
        unset($winning_ticket_ids_grouped[$old_prize]['manager_currency_ids'][$manager_currency_id][$user_currency_id]);
        if (empty($winning_ticket_ids_grouped[$old_prize]['manager_currency_ids'][$manager_currency_id])) {
            unset($winning_ticket_ids_grouped[$old_prize]['manager_currency_ids'][$manager_currency_id]);
        }
        if (empty($winning_ticket_ids_grouped[$old_prize]['manager_currency_ids'])) {
            unset($winning_ticket_ids_grouped[$old_prize]['manager_currency_ids']);
        }
        if (count($winning_ticket_ids_grouped[$old_prize]) === 1) {
            unset($winning_ticket_ids_grouped[$old_prize]);
        }
    }

    // TODO: {Vordis 2019-07-02 15:31:28} Probably filtering should be done in separate task, but atm I will not waste time on refractoring

    /**
     * Filter tickets into provided parameters.
     * NOTE: it will also merge slips into tickets.
     * NOTE2: slip size should have no impact on filtering.
     * NOTE3: will make ticket_with_uuid_and_currencies unusable.
     *
     * @param array $losing_ticket_ids
     * @param array $winning_ticket_ids
     * @param array $winning_tickets            winning tickets in form id => ['lcs_ticket' => , 'manager_currency_id'
     *                                          => , 'user_currency_id' => ] Needed by lines filtering.
     * @param array $winning_ticket_ids_grouped tickets grouped under their prizes to make as little updates as
     *                                          possible
     * @param array $prizes                     all unique prizes collected from tickets.
     * @param array $currency_ids               all unique prize currencies collected from tickets.
     * @param array $pay_out_uuids
     * @param array $pay_out_prizes
     *
     * @return void
     */
    private function filter_tickets(
        array &$losing_ticket_ids,
        array &$winning_ticket_ids,
        array &$winning_tickets,
        array &$winning_ticket_ids_grouped,
        array &$prizes,
        array &$currency_ids,
        array &$pay_out_uuids,
        array &$pay_out_prizes
    ): void
    {
        // NOTE: those are slips
        $lottery_tickets_lcs = $this->previous_task_result->get_data_item('lottery_tickets');
        $merged_lcs_tickets = [];
        $ticket_id_to_prize_map = [];

        for ($slip_counter = 0; $slip_counter < count($lottery_tickets_lcs); $slip_counter++) { // NOTE: lcs and with_uuids count should be the same
            // the problem here is that we operate on slips, not tickets
            // so we need to merge slips into tickets
            $whitelotto_ticket = &$this->ticket_with_uuid_and_currencies[$slip_counter];
            $lcs_ticket = &$lottery_tickets_lcs[$whitelotto_ticket['uuid']];
            $whitelotto_ticket_id = &$whitelotto_ticket['ticket_id'];
            $is_lost = $lcs_ticket['status'] === Helpers_General::TICKET_STATUS_NO_WINNINGS;
            $is_won = !$is_lost;
            $prize = floatval($lcs_ticket['prize']);

            if ($is_won && $this->should_payout($prize)) { // collect payout ticket uuids
                $pay_out_uuids[] = $lcs_ticket['uuid'];
            }

            // check if ticket already exists with such id
            if (isset($merged_lcs_tickets[$whitelotto_ticket_id])) { // merge values
                $existing_lcs_ticket = &$merged_lcs_tickets[$whitelotto_ticket_id];
                // merge slips into one ticket (add prizes, merge lines, change status if it's not already won)
                $existing_lcs_ticket['prize'] += $prize;
                if ($existing_lcs_ticket['status'] !== Helpers_General::TICKET_STATUS_WIN) { // block status on winning
                    $existing_lcs_ticket['status'] = $lcs_ticket['status'];
                }
                $existing_lcs_ticket['amount'] += $lcs_ticket['amount'];
                $existing_lcs_ticket['lottery_ticket_lines'] = array_merge($existing_lcs_ticket['lottery_ticket_lines'], $lcs_ticket['lottery_ticket_lines']);
                NumberHelper::round_currencies(
                    $existing_lcs_ticket['prize'],
                    $existing_lcs_ticket['amount']
                );
            } else {
                unset($existing_lcs_ticket);
                $merged_lcs_tickets[$whitelotto_ticket_id] = $lcs_ticket;
            }

            // collect user_id with their payout percent for later update
            $prize_payout_percent = $whitelotto_ticket['prize_payout_percent'] ?? 100;
            $this->user_ids_grouped_by_payout_percent[$prize_payout_percent] = array_merge(($this->user_ids_grouped_by_payout_percent[$prize_payout_percent] ?? []), [$whitelotto_ticket['user_id']]);

            if ($is_lost) {
                $is_whitelotto_ticket_won = isset($winning_tickets[$whitelotto_ticket_id]);
                $is_first_slip = !isset($existing_lcs_ticket);
                if ($is_first_slip) {
                    // collect lost only if it is first slip, next ones will be already marked or won
                    $losing_ticket_ids[$whitelotto_ticket_id] = $whitelotto_ticket_id;
                } else if ($is_whitelotto_ticket_won) { // not first slip (it has previous slips)
                    $winning_tickets[$whitelotto_ticket_id]['lcs_ticket'] = $existing_lcs_ticket; // refresh merged ticket. NOTE: it's crucial when we are on the last slip and it's lost.
                }
            } else if ($is_won) { // winning
                unset($losing_ticket_ids[$whitelotto_ticket_id]); // remove from losing collection in case of existing lost slips before
                $winning_ticket_ids[$whitelotto_ticket_id] = $whitelotto_ticket_id;
                // NOTE: values below will be safely overwritten
                $winning_tickets[$whitelotto_ticket_id] = [
                    'lcs_ticket' => $existing_lcs_ticket ?? $lcs_ticket, // existing ticket if it exists.
                    'user_currency_id' => $whitelotto_ticket['user_currency_id'],
                    'manager_currency_id' => $whitelotto_ticket['manager_currency_id'],
                ];
                // group results into construction: prize['whitelabel_currency_id']['user_currency_id']['ticket_ids']
                // meaning that they are grouped with aim at minimal amount of updates
                $user_currency_id = &$whitelotto_ticket['user_currency_id'];
                $manager_currency_id = &$whitelotto_ticket['manager_currency_id'];
                $ticket_id_to_prize_map[$whitelotto_ticket_id] = NumberHelper::round_currency(($ticket_id_to_prize_map[$whitelotto_ticket_id] ?? 0) + $prize); // NOTE: this map is needed to clear old entries for multiple winning slips.
                $ticket_prize = $ticket_id_to_prize_map[$whitelotto_ticket_id];
                $has_previous_winning_slip = $ticket_prize > $prize;
                if ($has_previous_winning_slip) {
                    $this->clear_for_winning_existent_ticket($ticket_id_to_prize_map, $winning_ticket_ids_grouped, $whitelotto_ticket_id, $user_currency_id, $manager_currency_id, $prize);
                }

                $winning_ticket_ids_grouped[$ticket_id_to_prize_map[$whitelotto_ticket_id]]['manager_currency_ids'][$manager_currency_id][$user_currency_id][$whitelotto_ticket_id] = $whitelotto_ticket_id;
                // add payout to grouped tickets and collect pay out users, for next task (balance)
                $payout = $this->should_payout($ticket_prize);
                $winning_ticket_ids_grouped[$ticket_prize]['payout'] = $payout;

                if ($payout) { // collect user ids and how much should be paid for them
                    // we need to take into account that single user can have multiple winning tickets

                    // NOTE: for prize_payout_percent functionality (and other that transforms prize value, we need value per whitelabel ticket, not lcs ticket). That's why we group below.
                    $pay_out_prizes[$whitelotto_ticket['user_id']]['currency'] = $this->currencies[$user_currency_id]['code'];
                    $pay_out_prizes[$whitelotto_ticket['user_id']]['lottery_currency'] = $this->currencies[$this->lottery_currency_id];
                    $pay_out_prizes[$whitelotto_ticket['user_id']]['prizes_by_tickets'][$whitelotto_ticket_id] = ($pay_out_prizes[$whitelotto_ticket['user_id']]['prizes_by_tickets'][$whitelotto_ticket_id] ?? 0) + $prize;
                    $pay_out_prizes[$whitelotto_ticket['user_id']]['prize_payout_percent'] = $prize_payout_percent; // TODO: {Vordis 2020-07-17 16:41:10} could be placed better - probably running too many times
                }

                // collect prizes and currencies - they will be joined with lines and calculated as whole
                $prizes[$ticket_prize] = $ticket_prize; // TODO: {Vordis 2019-11-13 15:42:25} perfectly we should also filter here but it's not critical (rather low impact on performance ultimately, nearly non existent atm)
                $currency_ids[$manager_currency_id] = $manager_currency_id;
                $currency_ids[$user_currency_id] = $user_currency_id;
            }
        }

        unset($this->user_ids_grouped_by_payout_percent[100]);
        unset($this->ticket_with_uuid_and_currencies); // we don't need it anymore
    }

    protected function push_prize_type_id($prize, array &$type_ids): void
    {
        if ($prize['winners'] !== '0') { // take into account only prizes with winners
            $type_ids[$prize['prizes']] = $prize['lottery_type_data_id'];
            if ($prize['is_jackpot']) {
                $this->jackpot_prize = $prize['prizes'];
            };
        }
    }

    /**
     * Initialize winning_lines_ids_grouped from $this->prizes.
     *
     * @return array prize => type_id
     */
    protected function get_type_ids(): array
    {
        $type_ids = [];
        foreach ($this->prizes as $prize) {
            $this->push_prize_type_id($prize, $type_ids);
        }
        unset($this->prizes); // not needed anymore

        return $type_ids;
    }

    /**
     * Filter winning tickets lines into prizes with their lines.
     *
     * @param array $lines
     * @param array $winning_tickets
     * @param array $winning_lines_ids_grouped winning lines grouped over common fields (prize and their currencies)
     *                                         to make as little updates as possible.
     * @param array $prizes                    pass prizes collected from tickets, they will be joined with lines.
     *
     * @return void
     */
    private function filter_lines(
        array $lines,
        array $winning_tickets,
        array &$winning_lines_ids_grouped,
        array &$prizes
    ): void
    {
        // IMPORTANT: lines order must be exactly the same as in which they were sent to lcs (and received from it)
        $last_ticket_id = null;
        $line_index = 0;
        foreach ($lines as $line) {
            if ($last_ticket_id !== $line['whitelabel_user_ticket_id']) {
                $last_ticket_id = $line['whitelabel_user_ticket_id'];
                $line_index = 0; // inner id for ticket
            }

            $lcs_line = &$winning_tickets[$last_ticket_id]['lcs_ticket']['lottery_ticket_lines'][$line_index++];

            if ($lcs_line['status'] === Helpers_General::TICKET_STATUS_WIN) {
                $this->prepare_winning_line($line, $lcs_line, $winning_tickets, $winning_lines_ids_grouped, $last_ticket_id, $prizes);
            }
        }
    }

    protected function prepare_winning_line($line, &$lcs_line, &$winning_tickets, &$winning_lines_ids_grouped, $last_ticket_id, &$prizes)
    {
        $type_ids = $this->type_ids ?: $this->type_ids = $this->get_type_ids();
        $prize = &$lcs_line['lottery_prize']['per_user'];
        $manager_currency_id = &$winning_tickets[$last_ticket_id]['manager_currency_id'];
        $user_currency_id = &$winning_tickets[$last_ticket_id]['user_currency_id'];
        $winning_lines_ids_grouped[$prize]['value'] = $lcs_line['lottery_prize']['per_user'];
        $winning_lines_ids_grouped[$prize]['payout'] = $this->should_payout($lcs_line['lottery_prize']['per_user']);
        $winning_lines_ids_grouped[$prize]['type_id'] = $type_ids[$prize]; // collect type id
        $winning_lines_ids_grouped[$prize]['manager_currency_ids'][$manager_currency_id][$user_currency_id][] = $line['id'];
        $prizes[$prize] = $prize; // collect line prize
    }

    /**
     * Base function for update tickets and lines.
     *
     * @param array    $calculated_prizes
     * @param array    $winning_ids_grouped
     * @param string   $model_class
     * @param \Closure $build_base_query function (string $prize, array $calculated_prizes, array
     *                                   &$type_and_manager_currency_ids): Database_Query_Builder_Update
     *
     * @return void
     */
    private function update_winning(array $calculated_prizes, array $winning_ids_grouped, string $model_class, \Closure $build_base_query): void
    {
        /**
         * @var Model_Whitelabel_User_Ticket|Model_Whitelabel_User_Ticket_Line $model_class
         */
        $sql = '';
        // ids are collected in this format: prize => manager_currency_id => user_currency_id => ticket_ids
        foreach ($winning_ids_grouped as $prize => $data) {
            // build base update query
            $this->query = $build_base_query($prize, $calculated_prizes, $data);
            // go over whitelabel (manger) currencies
            // NOTE: if prize has no currencies (can occur in lines) then it will be omitted by foreach
            foreach ($data['manager_currency_ids'] as $manager_currency_id => $user_currency_ids) {
                // set manager values
                $model_class::set_winning_manager(
                    $this->query,
                    $calculated_prizes[$prize][$manager_currency_id],
                    $this->lottery_provider
                );
                // set user values and execute query (query is effectively grouped over prize->manager->user)
                foreach ($user_currency_ids as $user_currency_id => $ids) {
                    $sql .= $model_class::set_winning_compile(
                            clone $this->query,
                            $calculated_prizes[$prize][$user_currency_id],
                            $ids,
                            $this->lottery_provider
                        ) . ';';
                }
            }
        }
        // TODO: {Vordis 2019-06-28 10:03:31} check how this works for big queries
        if ($sql === '') {
            throw new Exception("Cannot update winning " . get_class($model_class) . " because the query is empty.");
        }
        DB::query($sql)
            ->execute();
    }

    /**
     * Update winning lines.
     *
     * @param array $calculated_prizes
     * @param array $winning_lines_ids_grouped
     *
     * @return void
     */
    private function update_winning_lines(array $calculated_prizes, array $winning_lines_ids_grouped): void
    {
        $this->update_winning(
            $calculated_prizes,
            $winning_lines_ids_grouped,
            Model_Whitelabel_User_Ticket_Line::class,
            function (string $prize, array $calculated_prizes, array &$data): Database_Query_Builder_Update {
                // build base update query (with data_id and prize value)
                return Model_Whitelabel_User_Ticket_Line::set_winning_base(
                    $data['value'],
                    $calculated_prizes[$prize][Helpers_Currency::USD_ID],
                    $data['type_id'],
                    $this->lottery_provider,
                    $data['payout']
                );
            }
        );
    }

    /**
     * Check if jackpot was won.
     *
     * @return boolean
     */
    private function is_jackpot_won(): bool
    {
        return $this->jackpot_prize !== null;
    }

    /**
     * Update winning tickets.
     *
     * @param array $calculated_prizes
     * @param array $winning_ticket_ids_grouped
     *
     * @return void
     */
    private function update_winning_tickets(array $calculated_prizes, array $winning_ticket_ids_grouped): void
    {
        // establish closure arguments
        $get_is_prize_jackpot = $this->is_jackpot_won() ?
            function (string $prize): bool {
                return $prize >= $this->jackpot_prize; // prize is jackpot prize if it has equal or greater value (e.g. use won jackpot and last tier)
            } : function (): bool {
                return false; // jackpot wasn't won, so no need for more sophisticated check
            };

        $this->update_winning(
            $calculated_prizes,
            $winning_ticket_ids_grouped,
            Model_Whitelabel_User_Ticket::class,
            function (string $prize, array $calculated_prizes, array &$data) use ($get_is_prize_jackpot): Database_Query_Builder_Update {
                return Model_Whitelabel_User_Ticket::set_winning_base(
                    $prize,
                    $calculated_prizes[$prize][Helpers_Currency::USD_ID],
                    $this->lottery_provider,
                    $data['payout'],
                    $get_is_prize_jackpot($prize)
                );
            }
        );
    }

    private function update_payout_prize_percents_in_tickets(): void
    {
        foreach ($this->user_ids_grouped_by_payout_percent as $prize_payout_percent => $user_ids) {
            Model_Whitelabel_User_Ticket::update_payout_prize_percent_for_users(array_unique($user_ids), $prize_payout_percent);
        }
    }

    public function run(): void
    {
        // first filter winning tickets and losing tickets
        $losing_ticket_ids = $winning_ticket_ids = $winning_tickets = $winning_ticket_ids_grouped
            = $prizes = $currency_ids = $pay_out_uuids = $pay_out_prizes = [];
        $this->filter_tickets(
            $losing_ticket_ids,
            $winning_ticket_ids,
            $winning_tickets,
            $winning_ticket_ids_grouped,
            $prizes,
            $currency_ids,
            $pay_out_uuids,
            $pay_out_prizes
        );

        // if there are any winning tickets then update their values
        if (!empty($winning_ticket_ids)) {
            // load lines for winning tickets
            $lines_for_winning_tickets = Model_Whitelabel_User_Ticket_Line::by_ticket_ids($winning_ticket_ids)->as_array();
            // filter lines into winning ones under their prizes
            $winning_lines_ids_grouped = [];
            $this->filter_lines($lines_for_winning_tickets, $winning_tickets, $winning_lines_ids_grouped, $prizes);

            // we have filtered data and collected possible prizes and currencies
            // now calculate all possibilities (prize value in every currency)
            // done before ticket and line update to not recalculate these values
            $currency_ids[] = Helpers_Currency::USD_ID; // attach USD
            $calculated_prizes = Helpers_Currency::mass_calculate($prizes, $this->lottery_currency_id, $currency_ids, $this->currencies);
            unset($prizes, $currency_ids); // not needed anymore

            $this->update_winning_lines($calculated_prizes, $winning_lines_ids_grouped);

            // rest of the lines belonging to winning tickets will be updated to lost, based on ticket_id and status
            Model_Whitelabel_User_Ticket_Line::pending_to_lost_by_tickets($winning_ticket_ids);

            $this->update_winning_tickets($calculated_prizes, $winning_ticket_ids_grouped);

            $this->update_payout_prize_percents_in_tickets();
        }

        // if there are any losing tickets than update their values
        if (!empty($losing_ticket_ids)) {
            Model_Whitelabel_User_Ticket::set_as_lost_with_lines($losing_ticket_ids);
        }

        // set result for the paid out tasks
        $this->get_result()->put_data_item('pay_out_uuids', $pay_out_uuids);
        $this->get_result()->put_data_item('pay_out_prizes', $pay_out_prizes);
    }
}
