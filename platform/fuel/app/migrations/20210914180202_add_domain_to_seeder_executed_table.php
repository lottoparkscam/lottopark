<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Domain_To_Seeder_Executed_Table extends Database_Migration_Graceful
{
    private string $tableName = 'seeder_executed';

    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            $this->tableName,
            [
                'domain' => [
                    'type' => 'varchar',
                    'constraint' => 60,
                    'null' => false,
                    'after' => 'name',
                ],
            ]
        );

        Helper_Migration::drop_unique_key($this->tableName, ['name']);
        Helper_Migration::generate_unique_key($this->tableName, ['name', 'domain']);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::drop_unique_key($this->tableName, ['name', 'domain']);
        Helper_Migration::generate_unique_key($this->tableName, ['name']);
        DBUtil::drop_fields($this->tableName, [
            'domain'
        ]);
    }
}
