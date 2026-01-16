<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Update_lottery_draw_table_extend_numbers
{
    public function up()
    {
        DBUtil::modify_fields('lottery_draw', [
            'numbers' => [
                'type' => 'varchar',
                'constraint' => 255,
                'null' => true,
                'default' => null
            ],
        ]);
        DBUtil::modify_fields('lottery_draw', [
            'bnumbers' => [
                'type' => 'varchar',
                'constraint' => 255,
                'null' => true,
                'default' => null
            ],
        ]);
    }

    public function down()
    {
        DBUtil::modify_fields('lottery_draw', [
            'numbers' => [
                'type' => 'varchar',
                'constraint' => 30,
                'null' => true,
                'default' => null
            ],
        ]);
        DBUtil::modify_fields('lottery_draw', [
            'bnumbers' => [
                'type' => 'varchar',
                'constraint' => 30,
                'null' => true,
                'default' => null
            ],
        ]);
    }
}