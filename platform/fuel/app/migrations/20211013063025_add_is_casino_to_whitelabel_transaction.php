<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Is_Casino_To_Whitelabel_Transaction extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_transaction',
            [
                'is_casino' => [
                    'type' => 'tinyint',
                    'constraint' => 1,
                    'unsigned' => true,
                    'default' => 0
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_transaction',
            [
                'is_casino',
            ]
        );
    }
}