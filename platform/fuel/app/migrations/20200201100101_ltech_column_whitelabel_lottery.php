<?php

namespace Fuel\Migrations;

class Ltech_Column_Whitelabel_Lottery
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_lottery', [
            'ltech_lock' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_lottery', 'ltech_lock');
    }
}
