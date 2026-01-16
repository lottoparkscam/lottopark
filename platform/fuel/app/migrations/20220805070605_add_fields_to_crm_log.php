<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Fields_To_Crm_Log extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'crm_log',
            [
                'ip' => [
                    'type' => 'varchar',
                    'null' => true,
                    'default' => null,
                    'constraint' => 45,
                    'after' => 'data'
                ],
                'browser' => [
                    'type' => 'varchar',
                    'null' => true,
                    'default' => null,
                    'constraint' => 100,
                    'after' => 'ip'
                ],
                'operation_system' => [
                    'type' => 'varchar',
                    'null' => true,
                    'default' => null,
                    'constraint' => 100,
                    'after' => 'browser'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'crm_log',
            [
                'ip', 'browser', 'operation_system'
            ]
        );
    }
}
