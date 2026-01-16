<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Is_Reducing_Balance_Increases_Limits_Field_To_Whitelabel_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel', [
                'is_reducing_balance_increases_limits' => [
                    'type' => 'tinyint',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'max_daily_balance_change_per_user'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel', [
                'is_reducing_balance_increases_limits',
            ]
        );
    }
}