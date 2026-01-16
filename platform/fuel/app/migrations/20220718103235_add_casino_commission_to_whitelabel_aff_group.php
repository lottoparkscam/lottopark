<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Casino_Commission_To_Whitelabel_Aff_Group extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_aff_group';

    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            self::TABLE,
            [
                'casino_commission_value_manager' => [
                    'type' => 'decimal',
                    'constraint' => [5, 2],
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'ftp_commission_value_2_manager'
                ],
                'casino_commission_value_2_manager' => [
                    'type' => 'decimal',
                    'constraint' => [5, 2],
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'casino_commission_value_manager'
                ]
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            self::TABLE,
            [
                'casino_commission_value_manager',
                'casino_commission_value_2_manager'
            ]
        );
    }
}
