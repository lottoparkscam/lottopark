<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Add_Lottery_Provider_id_To_Whitelabel_Raffle
{
    public function up()
    {
        DBUtil::add_fields('whitelabel_raffle', [
            'lottery_provider_id' => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true]
        ]);
        DBUtil::add_foreign_key('whitelabel_raffle', [
            'constraint' => 'lottery_provider_id_idfx',
            'key'        => 'lottery_provider_id',
            'reference'  => [
                'table'  => 'lottery_provider',
                'column' => 'id'
            ]
        ]);
    }

    public function down()
    {
        DBUtil::drop_foreign_key('whitelabel_raffle', 'whitelabel_raffle_raffle_provider_id_foreign');
        DBUtil::drop_fields('whitelabel_raffle', ['lottery_provider_id']);
    }
}
