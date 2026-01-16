<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Login_Hash_And_Date_To_Whitelabel_User_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_user', [
            'login_hash' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => true,
                'default' => null,
                'after' => 'lost_last'
            ],
            'login_hash_created_at' => [
                'type' => 'datetime',
                'null' => true,
                'default' => null,
                'after' => 'login_hash'
            ],
            'login_by_hash_last' => [
                'type' => 'datetime',
                'null' => true,
                'default' => null,
                'after' => 'login_hash_created_at'
            ],
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_user', [
            'login_hash',
            'login_hash_created_at',
            'login_by_hash_last',
        ]);
    }
}