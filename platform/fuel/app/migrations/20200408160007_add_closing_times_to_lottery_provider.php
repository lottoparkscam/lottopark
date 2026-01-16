<?php

namespace Fuel\Migrations;

class Add_closing_times_to_lottery_provider
{
    public function up()
    {
        \DBUtil::add_fields('lottery_provider', [
            'closing_times' => [ 'type' => 'json', 'null' => true]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('lottery_provider', [
            'closing_times'
        ]);
    }
}
