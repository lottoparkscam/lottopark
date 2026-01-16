<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Add_analytics_casino_to_whitelabel
{
    public function up()
    {
        DBUtil::add_fields('whitelabel', array(
            'analytics_casino' => array('type' => 'varchar', 'constraint' => 45, 'null' => true),
        ));
    }

    public function down()
    {
        DBUtil::drop_fields('whitelabel', array(
            'analytics_casino',
        ));
    }
}
