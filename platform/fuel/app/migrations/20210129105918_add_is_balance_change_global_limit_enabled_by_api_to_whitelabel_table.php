<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Is_Balance_Change_Global_Limit_Enabled_By_Api_To_Whitelabel_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel', [
                'is_balance_change_global_limit_enabled_in_api' => [
                    'type' => 'tinyint',
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'is_reducing_balance_increases_limits'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel', [
                'is_balance_change_global_limit_enabled_in_api',
            ]
        );
    }
}