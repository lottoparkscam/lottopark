<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Update_whitelabel_user_ticket_keno_data_table_extend_id
{
    public function up()
    {
        DBUtil::modify_fields('whitelabel_user_ticket_keno_data', [
            'id' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
                'auto_increment' => true
            ],
        ]);
    }

    public function down()
    {
        DBUtil::modify_fields('whitelabel_user_ticket_keno_data', [
            'id' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => false,
                'auto_increment' => true
            ],
        ]);
    }
}