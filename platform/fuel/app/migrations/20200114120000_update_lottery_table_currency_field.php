<?php

namespace Fuel\Migrations;

class Update_Lottery_Table_Currency_Field
{
    public function up()
    {
        \DBUtil::add_fields('lottery', [
            'force_currency' => ['type' => 'varchar', 'constraint' => 3, 'null' => true, 'default' => null]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('lottery', [
            'force_currency'
        ]);
    }
}
