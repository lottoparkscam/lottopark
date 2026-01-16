<?php

namespace Fuel\Migrations;

class Update_Whitelabel_Us_State
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel', [
            'us_state_active' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'null' => true, 'default' => 0, 'after' => 'theme'],
            'enabled_us_states' => ['type' => 'text', 'null' => true, 'default' => null, 'after' => 'us_state_active'],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel', 'us_state_active');
        \DBUtil::drop_fields('whitelabel', 'enabled_us_states');
    }
}
