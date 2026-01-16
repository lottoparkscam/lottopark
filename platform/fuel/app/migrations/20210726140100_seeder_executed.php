<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;
use Helper_Migration;

class Seeder_Executed
{
    public function up()
    {
        $tableName = 'seeder_executed';

        DBUtil::create_table(
            $tableName,
            [
            'id' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'name' => [
                'type' => 'varchar',
                'constraint' => 100,
            ],
            'created_at' => [
                'type' => 'date'
            ],
        ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
        );
        
        Helper_Migration::generate_unique_key($tableName, ['name']);
    }

    public function down()
    {
        DBUtil::drop_table('seeder_executed');
    }
}
