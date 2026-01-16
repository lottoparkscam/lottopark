<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Alter_Whitelabel_Ticket_Token
{
    public function up()
    {
        DBUtil::modify_fields('whitelabel_raffle_ticket', [
            'token' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true]
        ]);
    }

    public function down()
    {
        DBUtil::modify_fields('whitelabel_raffle_ticket', [
            'token' => ['type' => 'varchar', 'constraint' => 255]
        ]);
    }
}
