<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Add_sell_dates_raffle_rule
{
    public function up()
    {
        DBUtil::add_fields(
            'raffle_rule',
            [
                'is_sell_enabled' => ['type' => 'bool', 'default' => true],
                'is_sell_limitation_enabled' => ['type' => 'bool', 'default' => false],
                'sell_open_dates' => ['type' => 'json', 'null' => true]
            ]
        );
    }

    public function down()
    {
        DBUtil::drop_fields('raffle_rule', ['is_sell_enabled', 'is_sell_limitation_enabled', 'sell_open_dates']);
    }
}
