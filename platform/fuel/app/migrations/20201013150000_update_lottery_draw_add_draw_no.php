<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Update_lottery_draw_add_draw_no
{
    public function up()
    {
        DBUtil::add_fields('lottery_draw', [
            'draw_no' => [
                'type' => 'int',
                'constraint' => 10,
                'null' => true,
                'default' => null,
                'unsigned' => true,
                'after' => 'lottery_type_id',
            ],
        ]);
    }

    public function down()
    {
        DBUtil::drop_fields('lottery_draw', ['draw_no']);
    }
}
