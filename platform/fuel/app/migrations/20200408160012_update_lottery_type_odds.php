<?php

namespace Fuel\Migrations;

class Update_lottery_type_odds
{
    public function up()
    {
        \DBUtil::modify_fields('lottery_type', [
            'odds' => [
                'constraint' => [9, 2],
                'type' => 'decimal',
                'unsigned' => true
            ],
        ]);
    }

    public function down()
    {
        \DBUtil::modify_fields('lottery_type', [
            'odds' => [
                'constraint' => [4, 2],
                'type' => 'decimal',
                'unsigned' => true
            ],
        ]);
    }
}
