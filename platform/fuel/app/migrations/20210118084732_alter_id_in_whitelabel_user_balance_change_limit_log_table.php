<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

class alter_Id_In_Whitelabel_User_Balance_Change_Limit_Log_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields('whitelabel_user_balance_change_limit_log', [
            'id' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true
            ],
        ]);
    }

    /**
     * Revert migration.
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::modify_fields('raffle_rule_tier', [
            'id' => [
                'type' => 'int',
                'constraint' => 3,
                'unsigned' => true,
                'auto_increment' => true
            ],
        ]);
    }
}
