<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class IpLoginTryChangeColumnName extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            'ip_login_try',
            [
                'last_login_try' => [
                    'type' => 'datetime',
                    'name' => 'last_login_try_at'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            'ip_login_try',
            [
                'last_login_try_at' => [
                    'type' => 'datetime',
                    'name' => 'last_login_try'
                ],
            ]
        );
    }
}
