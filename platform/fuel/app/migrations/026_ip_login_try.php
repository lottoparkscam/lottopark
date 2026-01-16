<?php

namespace Fuel\Migrations;

class Ip_Login_Try
{
    public function up()
    {
        \DBUtil::create_table(
            'ip_login_try',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'ip' => ['type' => 'varchar', 'constraint' => 45],
                'last_login_try' => ['type' => 'datetime'],
                'login_try_count' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );

        \DBUtil::create_index('ip_login_try', 'ip', 'ip_login_try_ip_idx', 'UNIQUE');

    }

    public function down()
    {
        \DBUtil::drop_table('ip_login_try');
    }
}
