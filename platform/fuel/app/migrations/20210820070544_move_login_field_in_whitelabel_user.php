<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Move_Login_Field_In_Whitelabel_User extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields('whitelabel_user', [
            'login' => ['type' => 'varchar', 'constraint' => 100, 'null' => true, 'after' => 'email']
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields('whitelabel_user', [
            'login' => ['type' => 'varchar', 'constraint' => 100, 'null' => true, 'after' => 'connected_aff_id']
        ]);
    }
}