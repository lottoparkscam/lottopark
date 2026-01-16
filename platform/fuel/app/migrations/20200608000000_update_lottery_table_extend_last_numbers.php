<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Update_lottery_table_extend_last_numbers
{
    public function up()
    {
        DBUtil::modify_fields('lottery', [
            'last_numbers' => [
                'type' => 'varchar',
                'constraint' => 256,
                'null' => true,
            ],
            'last_bnumbers' => [
                'type' => 'varchar',
                'constraint' => 256,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        DBUtil::modify_fields('lottery', [
            'last_numbers' => [
                'type' => 'varchar',
                'constraint' => 30,
                'null' => true,
            ],
            'last_bnumbers' => [
                'type' => 'varchar',
                'constraint' => 30,
                'null' => true,
            ],
        ]);
    }
}