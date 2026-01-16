<?php

namespace Fuel\Migrations;

class Add_min_total_purchase_to_whitelabel_bonus
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_bonus', [
            'min_total_purchase' => ['type' => 'decimal', 'constraint' => [9, 2], 'null' => true, 'unsigned' => true]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_bonus', [
            'min_total_purchase'
        ]);
        
        \DB::delete('bonus')->where(['id' => 2])->execute();
    }
}
