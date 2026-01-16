<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\WhitelabelAff;
use Models\WhitelabelUser;

final class Rename_Columns_With_Numbers extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            WhitelabelAff::get_table_name(),
            [
                'address1' => [
                    'name' => 'address_1',
                    'type' => 'varchar',
                    'constraint' => 100
                ],
                'address2' => [
                    'name' => 'address_2',
                    'type' => 'varchar',
                    'constraint' => 100
                ],
            ]
        );
        DBUtil::modify_fields(
            WhitelabelUser::get_table_name(),
            [
                'address1' => [
                    'name' => 'address_1',
                    'type' => 'varchar',
                    'constraint' => 100
                ],
                'address2' => [
                    'name' => 'address_2',
                    'type' => 'varchar',
                    'constraint' => 100
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            WhitelabelAff::get_table_name(),
            [
                'address_1' => [
                    'name' => 'address1',
                    'type' => 'varchar',
                    'constraint' => 100
                ],
                'address_2' => [
                    'name' => 'address2',
                    'type' => 'varchar',
                    'constraint' => 100
                ],
            ]
        );
        DBUtil::modify_fields(
            WhitelabelUser::get_table_name(),
            [
                'address_1' => [
                    'name' => 'address1',
                    'type' => 'varchar',
                    'constraint' => 100
                ],
                'address_2' => [
                    'name' => 'address2',
                    'type' => 'varchar',
                    'constraint' => 100
                ],
            ]
        );
    }
}