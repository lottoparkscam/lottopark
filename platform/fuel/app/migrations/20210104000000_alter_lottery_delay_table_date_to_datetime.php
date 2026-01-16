<?php

namespace Fuel\Migrations;

use Fuel\Core\DB;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Alter_Lottery_Delay_Table_Date_To_Datetime extends \Database_Migration_Graceful
{
    //TODO: Update_lottery_delay_date_local_date_delay
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields('lottery_delay', [
            'date_local' => ['type' => 'datetime'],
            'date_delay' => ['type' => 'datetime']
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields('lottery_delay', [
            'date_local' => ['type' => 'date'],
            'date_delay' => ['type' => 'date']
        ]);
    }
}