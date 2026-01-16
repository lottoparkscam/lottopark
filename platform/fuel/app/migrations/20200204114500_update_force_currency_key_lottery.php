<?php

namespace Fuel\Migrations;

class Update_Force_Currency_Key_Lottery
{
    public function up()
    {
        \DBUtil::drop_fields('lottery', [
            'force_currency'
        ]);

        \DBUtil::add_fields('lottery', [
            'force_currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null]
        ]);

        \DBUtil::add_foreign_key('lottery', [
            'constraint' => 'lottery_force_currency_id_currency',
            'key' => 'force_currency_id',
            'reference' => [
                'table' => 'currency',
                'column' => 'id'
            ],
            'on_update' => 'NO ACTION',
            'on_delete' => 'NO ACTION'
        ]);

        \DBUtil::create_index('lottery', 'force_currency_id', 'lottery_force_currency_id_currency');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottery', 'lottery_force_currency_id_currency');

        \DBUtil::drop_fields('lottery', [
            'force_currency_id'
        ]);
        
        \DBUtil::add_fields('lottery', [
            'force_currency' => ['type' => 'varchar', 'constraint' => 3, 'null' => true, 'default' => null]
        ]);
    }
}
