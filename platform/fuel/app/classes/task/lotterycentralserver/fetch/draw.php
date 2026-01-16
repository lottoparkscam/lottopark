<?php

use Carbon\Carbon;

/**
 * Fetch last draw data from Lottery Central Server.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-11
 * Time: 11:06:56
 */
abstract class Task_Lotterycentralserver_Fetch_Draw extends Task_Lotterycentralserver_Fetch_Task
{
    /**
     * Last draw from LCS is different than last draw at whitelotto.
     */
    const OUTDATED = 1;

    /**
     * There is no difference between LCS and whitelotto.
     */
    const UP_TO_DATE = 0;

    /**
     * Fetch a batch of last draws for specified lottery from LCS.
     *
     * @param int|null                $limit how many draws should be fetched (batch size)
     *
     * @param Model_Lottery_Draw|null $last_draw
     *
     * @return Response_Interface should contain: last draws with attached lottery_prizes,
     * with lottery_rule_tier to which they belong attached.
     * @throws Throwable
     */
    public function fetch(?int $limit = 10, ?Model_Lottery_Draw $last_draw = null): Response_Interface
    {
        // prepare necessary parameters for communication
        $lottery = $this->get_lottery();
        if (is_null($last_draw)) {
            $last_draw = Model_Lottery_Draw::last_for_lottery_by_draw_no($lottery->id);
        }
        $last_draw_no = $last_draw['draw_no'] ?? null;
        $route = "lottery/draws";
        $get = [];
        if (!is_null($last_draw_no)) {
            $get[] = "draw_no=" . $last_draw_no;
        }
        if (!is_null($limit)) {
            $get[] = "limit=" . $limit;
        }
        if (!empty($get)) {
            $route = $route . "?" . implode("&", $get);
        }
        $endpoint_url = parent::absolute_url($route);
        Helpers_Cli::writeln("Fetch last draws endpoint URL: " . $endpoint_url);
        $headers = parent::headers($route, Helpers_Lottery::get_slug($lottery->id));

        // query LCS via Curl and return decoded result
        $response = Services_Curl::get_json($endpoint_url, $headers);
        Helpers_Cli::writeln("LCS responded: " . $response);
        $drawsFromResponse = json_decode($response);
        if ($drawsFromResponse === null) {
            throw new \Exception("Lcs returned invalid response - unable to decode into json");
        }

        return Response_Lcs_Lottery_Last_Draws::build_from_json(json_encode(['draws' => $drawsFromResponse]));
    }
}
