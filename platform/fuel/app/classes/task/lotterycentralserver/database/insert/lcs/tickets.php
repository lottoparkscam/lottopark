<?php

/**
 * Insert Lottery Central Server Tickets
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-24
 * Time: 10:10:52
 */
final class Task_Lotterycentralserver_Database_Insert_Lcs_Tickets extends Task_Lotterycentralserver_Database_Task
{

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     * @return void
     */
    public function run(): void
    { // TODO: {Vordis 2019-06-24 10:33:33} batch insert probably could be defined for models, so it will be much easier to use them
        $tickets = $this->previous_task_result
            ->get_data_item('lcs_response')['lottery_tickets'];
        $request_payload = $this->previous_task_result
            ->get_data_item('request_payload')['tickets'];

        // prepare an insert statement
        $this->query = DB::insert('lcs_ticket');

        // Set the columns
        $this->query->columns(
            [
                'whitelabel_user_ticket_slip_id',
                'uuid',
            ]
        );

        $index = 0;
        // Set the values
        foreach ($request_payload as $whitelabel_user_ticket_slip_id => $value) { // we want only keys, without overhead from array_keys
            $this->query->values(
                [
                    $whitelabel_user_ticket_slip_id,
                    $tickets[$index++]['uuid'],
                ]
            );
        }

        $this->query->execute();
    }
}
