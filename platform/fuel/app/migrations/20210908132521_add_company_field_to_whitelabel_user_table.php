<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Company_Field_To_Whitelabel_User_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_user',
            [
                'company' => [
                    'type' => 'varchar',
                    'constraint' => 100,
                    'default' => null,
                    'null' => true,
                    'after' => 'phone'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_user',
            [
                'company',
            ]
        );
    }
}