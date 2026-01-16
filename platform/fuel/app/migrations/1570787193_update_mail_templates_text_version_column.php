<?php

namespace Fuel\Migrations;

class Update_Mail_Templates_Text_Version_Column
{
    public function up()
    {
        \DBUtil::add_fields('mail_templates', [
            'text_content' => ['type' => 'text', 'null' => true, 'after' => 'content']
        ]);

        \DBUtil::add_fields('whitelabel_mail_templates', [
            'text_content' => ['type' => 'text', 'null' => true, 'after' => 'content']
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('mail_templates', 'text_content');
        \DBUtil::drop_fields('whitelabel_mail_templates', 'text_content');
    }
}
