<?php

namespace Fuel\Tasks;

use Task_Cli;

class Send_Multi_Draw_Notifications extends Task_Cli
{

    public function __construct()
    {
        $this->disableOnProduction();
    }

    public function run()
    {
        $to_sent = \DB::query("SELECT mt.* FROM multi_draw mt
            LEFT JOIN whitelabel_transaction wt ON wt.id = mt.whitelabel_transaction_id
            WHERE wt.status = 1
            AND current_draw = valid_to_draw
            AND is_notification_sent = 0
            LIMIT 10
        ")->execute();

        foreach($to_sent AS $notification) {
            \Helpers_Multidraw::send_notification($notification);
        }
    }
}

?>