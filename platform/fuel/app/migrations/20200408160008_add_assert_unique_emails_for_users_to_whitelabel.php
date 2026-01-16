<?php

namespace Fuel\Migrations;

class Add_assert_unique_emails_for_users_to_whitelabel
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel',
            ['assert_unique_emails_for_users' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 1]]
        );
    }

    public function down()
    {
        \DBUtil::drop_fields(
            'whitelabel',
            ['assert_unique_emails_for_users']
        );
    }
}
