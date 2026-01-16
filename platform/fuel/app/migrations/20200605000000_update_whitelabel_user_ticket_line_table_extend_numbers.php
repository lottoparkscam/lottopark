<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Update_whitelabel_user_ticket_line_table_extend_numbers
{
    public function up()
    {
        DBUtil::modify_fields('whitelabel_user_ticket_line', [
            'numbers' => [
                'type' => 'varchar',
                'constraint' => 256,
            ],
            'bnumbers' => [
                'type' => 'varchar',
                'constraint' => 256,
            ],
        ]);
    }

    public function down()
    {
        DBUtil::modify_fields('whitelabel_user_ticket_line', [
            'numbers' => [
                'type' => 'varchar',
                'constraint' => 30,
            ],
            'bnumbers' => [
                'type' => 'varchar',
                'constraint' => 30,
            ],
        ]);
    }
}