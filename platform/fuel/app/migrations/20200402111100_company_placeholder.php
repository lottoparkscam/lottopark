<?php

namespace Fuel\Migrations;

class Company_Placeholder
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel', [
            'company_details' => ['type' => 'text', 'null' => true, 'after' => 'realname'],
        ]);
    }

    public function down()
    {
        \DBUtil::drop_fields('whitelabel', 'company_details');
    }
}