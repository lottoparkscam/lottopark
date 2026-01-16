<?php

namespace Fuel\Migrations;

class Update_lottery_last_date_local_next_date_local
{
    public function up()
    {
        \DBUtil::modify_fields('lottery', [
            'last_date_local' => [
                'type' => 'datetime',
                'null' => true,
                'default' => null,
            ],
            'next_date_local' => [
                'type' => 'datetime',
                'null' => true,
                'default' => null,
            ],
        ]);
    }

    public function down()
    {
        \DBUtil::modify_fields('lottery', [
            'last_date_local' => [
                'type' => 'date',
                'null' => true,
                'default' => null,
            ],
            'next_date_local' => [
                'type' => 'date',
                'null' => true,
                'default' => null,
            ],
        ]);
    }
}


