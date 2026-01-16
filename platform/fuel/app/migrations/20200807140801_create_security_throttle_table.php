<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Create_security_throttle_table
{
    public function up()
    {
        DBUtil::create_table('security_throttle', [
            'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'ip'         => ['type' => 'varchar', 'constraint' => 45],
            'resource'   => ['type' => 'varchar', 'constraint' => 150],
            'created_at' => ['type' => 'datetime'],
        ], ['id']);
    }

    public function down()
    {
        DBUtil::drop_table('security_throttle');
    }
}
