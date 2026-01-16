<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Update_lottery_type_add_date_end
{
    public function up()
    {
        DBUtil::add_fields('lottery_type', [
            'date_end' => [
                'type' => 'date',
                'null' => true,
                'default' => null,
                'after' => 'date_start',
            ],
        ]);

    }

    public function down()
    {
        DBUtil::drop_fields('lottery_type', ['date_end']);
    }
}
