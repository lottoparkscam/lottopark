<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\Whitelabel;

final class Add_Default_Casino_Commission_To_Whitelabel extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            Whitelabel::get_table_name(),
            [
                'def_casino_commission_value_manager' => [
                    'type' => 'decimal',
                    'constraint' => [8, 2],
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'def_ftp_commission_value_2_manager'
                ],
                'def_casino_commission_value_2_manager' => [
                    'type' => 'decimal',
                    'constraint' => [8, 2],
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'def_casino_commission_value_manager'
                ]
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            Whitelabel::get_table_name(),
            [
                'def_casino_commission_value_manager',
                'def_casino_commission_value_2_manager',
            ]
        );
    }
}
