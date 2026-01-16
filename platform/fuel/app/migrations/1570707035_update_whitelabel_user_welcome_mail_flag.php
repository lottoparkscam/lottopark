<?php

namespace Fuel\Migrations;

class Update_Whitelabel_User_Welcome_Mail_Flag
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel_user', [
            'sent_welcome_mail' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0, 'after' => 'first_purchase'],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel_user', 'sent_welcome_mail');
    }
}
