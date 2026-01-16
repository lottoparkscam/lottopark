<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Use_Register_Company_Field_To_Whitelabel_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel',
            [
                'use_register_company' => [
                    'type' => 'tinyint',
                    'default' => 0,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'register_phone'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel',
            [
                'use_register_company',
            ]
        );
    }
}