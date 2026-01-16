<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Update_lottery_table_add_type
{
    public function up()
    {
        DBUtil::add_fields('lottery', [
            'type' => [
                'type' => 'varchar',
                'constraint' => 40,
                'default' => 'lottery',
                'after' => 'name',
            ],
        ]);
    }

    public function down()
    {
        DBUtil::drop_fields('lottery', [
            'type'
        ]);
    }
}