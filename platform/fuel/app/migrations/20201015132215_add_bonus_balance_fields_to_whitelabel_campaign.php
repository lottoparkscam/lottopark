<?php

namespace Fuel\Migrations;

class Add_bonus_balance_fields_to_whitelabel_campaign
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel_campaign',
            [
                'bonus_balance_amount' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'bonus_balance_type' => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'default' => null]
            ]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_campaign', [
            'bonus_balance_amount',
            'bonus_balance_type'
        ]);
    }
}
