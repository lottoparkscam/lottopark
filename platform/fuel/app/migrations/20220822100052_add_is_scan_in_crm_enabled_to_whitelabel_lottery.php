<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\WhitelabelLottery;

final class Add_Is_Scan_In_Crm_Enabled_To_Whitelabel_Lottery extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            WhitelabelLottery::get_table_name(),
            [
                'is_scan_in_crm_enabled' => [
                    'type' => 'boolean',
                    'default' => true,
                    'after' => 'should_decrease_prepaid'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            WhitelabelLottery::get_table_name(),
            [
                'is_scan_in_crm_enabled',
            ]
        );
    }
}
