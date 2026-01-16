<?php

namespace Fuel\Migrations;

class Should_Decrease_Prepaid_Whitelabel_Lottery
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_lottery', [
            'should_decrease_prepaid' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_lottery', 'should_decrease_prepaid');
    }
}
