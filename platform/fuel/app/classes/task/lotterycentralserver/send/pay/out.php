<?php

/**
* Send to LCS request to mark specified tickets as paid out.
* @author Marcin Klimek <marcin.klimek at gg.international>
* Date: 2019-07-02
* Time: 15:02:32
*/
final class Task_Lotterycentralserver_Send_Pay_Out extends Task_Lotterycentralserver_Send_Task
{

    /**
     * Previous task result.
     * @var Task_Result
     */
    private $previous_task_result;

    private int $lotteryId;
    
    /**
    * Create new instance of send pay out task.
    *
    * @param Task_Result $previous_task_result Previous task result.
     */
    public function __construct(Task_Result $previous_task_result, int $lotteryId)
    {
        parent::__construct();
        $this->previous_task_result = $previous_task_result;
        $this->lotteryId = $lotteryId;
    }
    
    /**
     * Send request mark tickets as paid out to Lottery Central Server.
     *
     * @return array Lottery Central Server response.
     */
    private function send_pay_out_request(): array
    {
        $request_payload = [
            'uuids' => $this->previous_task_result->get_data_item('pay_out_uuids')
        ];
        // prepare necessary parameters for communication
        $route = "lottery/tickets/paid_out";
        $endpoint_url = parent::absolute_url($route);
        $headers = parent::headers($route, Helpers_Lottery::get_slug($this->lotteryId), $request_payload);

        // query LCS via Curl and return decoded result
        $response = Services_Curl::post_json($endpoint_url, $request_payload, $headers);

        return json_decode($response, true);
    }

    public function run(): void
    {
        // proceed only if there are any winning tickets
        if (empty($this->previous_task_result->get_data_item('pay_out_uuids'))) {
            return;
        }

        $mark_paid_out_result = $this->send_pay_out_request();

        parent::evaluate_response($mark_paid_out_result, 'updated_count');

        $uuids_count = count($this->previous_task_result->get_data_item('pay_out_uuids'));
        if ($uuids_count !== (int) $mark_paid_out_result['updated_count']) {
            throw new \Exception('LCS update count differ from sent items count!');
        }
    }
}