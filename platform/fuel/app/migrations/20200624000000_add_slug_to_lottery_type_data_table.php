<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Add_slug_to_lottery_type_data_table
{
    public function up()
    {
        DBUtil::add_fields(
            'lottery_type_data',
            [
                'slug' => [
                    'type' => 'varchar',
                    'constraint' => 40,
                    'null' => true,
                    'after' => 'lottery_type_id',
                ]
            ]
        );
    }

    public function down()
    {
        DBUtil::drop_fields('lottery_type_data', [
            'slug'
        ]);
    }
}
