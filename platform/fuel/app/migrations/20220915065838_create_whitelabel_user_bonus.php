<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Whitelabel_User_Bonus extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            'whitelabel_user_bonus',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'bonus_id' => ['type' => 'tinyint', 'unsigned' => true],
                'type' => ['type' => 'varchar', 'constraint' => 15],
                'lottery_type' => ['type' => 'varchar', 'constraint' => 15],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'used_at' => ['type' => 'datetime', 'null' => true, 'default' => null],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key('whitelabel_user_bonus', 'bonus_id'),
                Helper_Migration::generate_foreign_key('whitelabel_user_bonus', 'whitelabel_user_id'),
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('whitelabel_user_bonus');
    }
}
