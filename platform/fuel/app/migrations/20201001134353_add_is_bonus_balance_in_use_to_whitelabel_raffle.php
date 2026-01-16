<?php

namespace Fuel\Migrations;

class add_is_bonus_balance_in_use_to_whitelabel_raffle
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel_raffle',
            ['is_bonus_balance_in_use' => ['type' => 'bool', 'default' => false, 'after' => 'is_enabled']]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields(
            'whitelabel_raffle',
            ['is_bonus_balance_in_use']
        );
    }
}
