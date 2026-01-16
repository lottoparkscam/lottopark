<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Move_sell_dates_from_raffle_rule_to_raffle
{
    public function up()
    {
        DBUtil::drop_fields('raffle_rule', ['is_sell_enabled', 'is_sell_limitation_enabled', 'sell_open_dates']);

        DBUtil::add_fields(
            'raffle',
            [
                'is_sell_enabled' => ['type' => 'bool', 'default' => true, 'after' => 'is_enabled'],
                'is_sell_limitation_enabled' => ['type' => 'bool', 'default' => false, 'after' => 'is_sell_enabled'],
                'sell_open_dates' => ['type' => 'json', 'null' => true, 'after' => 'is_sell_limitation_enabled']
            ]
        );
    }

    public function down()
    {
        DBUtil::add_fields(
            'raffle_rule',
            [
                'is_sell_enabled' => ['type' => 'bool', 'default' => true],
                'is_sell_limitation_enabled' => ['type' => 'bool', 'default' => false],
                'sell_open_dates' => ['type' => 'json', 'null' => true]
            ]
        );

        DBUtil::drop_fields('raffle', ['is_sell_enabled', 'is_sell_limitation_enabled', 'sell_open_dates']);
    }
}
