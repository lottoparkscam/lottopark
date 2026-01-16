<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Show_Categories_Field_To_Whitelabel_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel',
            [
                'show_categories' => [
                    'type' => 'tinyint',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'show_ok_in_welcome_popup'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel',
            [
                'show_categories',
            ]
        );
    }
}
