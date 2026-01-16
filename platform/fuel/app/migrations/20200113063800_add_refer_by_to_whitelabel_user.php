<?php

namespace Fuel\Migrations;

class Add_refer_by_to_whitelabel_user
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_user', [
            'refer_by' => ['type' => 'integer', 'constraint' => 10, 'null' => true, 'unsigned' => true]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_user', [
            'refer_by'
        ]);
    }
}
