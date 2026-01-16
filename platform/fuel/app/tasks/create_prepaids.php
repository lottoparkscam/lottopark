<?php

namespace Fuel\Tasks;

use Fuel\Core\DB;
use Task_Cli;

/**
 * This is intended to be run by experienced dev
 * It regenerates all pre-paid records
 * There should be valid pre-paid manual adjustments records to be left
 */
class Create_Prepaids extends Task_Cli
{
    public function __construct()
    {
        $this->disableOnProduction();
    }

    public function run(): void
    {
        set_time_limit(0);
        // this is list of valid prepaid ID-s
        $whitelisted_records = [1,2,222,500,2543,2544,2545,2565,2601,2604,2929,2991,3332,3333,3348,3476,3584,3696,3807,3858];
        DB::query("DELETE FROM whitelabel_prepaid WHERE id NOT IN (".implode(",", $whitelisted_records).")")->execute();
        $pending_prepaids = DB::query("SELECT wut.*, wt.date_confirmed, (SELECT COUNT(*) FROM whitelabel_user_ticket_slip wuts WHERE wuts.whitelabel_user_ticket_id = wut.id AND wuts.whitelabel_ltech_id IS NOT NULL) AS slips_count
        FROM whitelabel_user_ticket wut
        LEFT JOIN whitelabel w ON w.id = wut.whitelabel_id
        LEFT JOIN whitelabel_transaction wt ON wt.id = wut.whitelabel_transaction_id
        WHERE w.type = 2
        AND wut.paid = 1
        HAVING slips_count = 0")->execute()->as_array();

        foreach ($pending_prepaids as $pending_prepaid) {
            if ($pending_prepaid['cost_manager'] == '0.00') {
                continue;
            }
            $prepaid_for_transaction = null;
            if (!empty($pending_prepaid['whitelabel_transaction_id'])) {
                $prepaid_for_transaction = \Model_Whitelabel_Prepaid::find_by_whitelabel_transaction_id($pending_prepaid['whitelabel_transaction_id']);
                if ($prepaid_for_transaction !== null && count($prepaid_for_transaction)) {
                    $prepaid_for_transaction = $prepaid_for_transaction[0];
                } else {
                    $prepaid_for_transaction = null;
                }
            }
            if ($prepaid_for_transaction === null) {
                $prepaid = \Model_Whitelabel_Prepaid::forge();
                $prepaid->set([
                    "whitelabel_id" => $pending_prepaid['whitelabel_id'],
                    "date" => $pending_prepaid['date_confirmed'] ?? $pending_prepaid['date'],
                    "amount" => -$pending_prepaid['cost_manager'],
                    "whitelabel_transaction_id" => $pending_prepaid['whitelabel_transaction_id'] ?? null
                ]);
                $prepaid->save();
            } else {
                $prepaid_for_transaction->amount -= $pending_prepaid['cost_manager'];
                $prepaid_for_transaction->save();
            }
        }

        DB::query("UPDATE whitelabel w SET w.prepaid = (SELECT COALESCE(SUM(wp.amount), 0) FROM whitelabel_prepaid wp WHERE wp.whitelabel_id = w.id) WHERE w.type = 2")->execute();
    }
}
