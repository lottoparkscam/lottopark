<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\Whitelabel;

final class Update_Casino_Commission_Columns_From_Whitelabel extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            Whitelabel::get_table_name(),
            [
                'def_casino_commission_value_manager' => [
                    'name' => 'default_casino_commission_percentage_value_for_tier_1',
                    'type' => 'decimal',
                    'constraint' => [5, 2],
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'def_ftp_commission_value_manager'
                ],
                'def_casino_commission_value_2_manager' => [
                    'name' => 'default_casino_commission_percentage_value_for_tier_2',
                    'type' => 'decimal',
                    'constraint' => [5, 2],
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'def_ftp_commission_value_2_manager'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            Whitelabel::get_table_name(),
            [
                'default_casino_commission_percentage_value_for_tier_1' => [
                    'name' => 'def_casino_commission_value_manager',
                    'type' => 'decimal',
                    'constraint' => [8, 2],
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'def_ftp_commission_value_manager'
                ],
                'default_casino_commission_percentage_value_for_tier_2' => [
                    'name' => 'def_casino_commission_value_2_manager',
                    'type' => 'decimal',
                    'constraint' => [8, 2],
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'def_ftp_commission_value_2_manager'
                ]
            ]
        );
    }
}
