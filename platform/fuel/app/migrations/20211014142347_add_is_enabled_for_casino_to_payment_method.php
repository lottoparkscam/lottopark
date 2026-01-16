<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Is_Enabled_For_Casino_To_Payment_Method extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'payment_method',
            [
                'is_enabled_for_casino' => [
                    'type' => 'tinyint',
                    'constraint' => 1,
                    'unsigned' => true,
                    'default' => 1,
                    'after' => 'name',
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'payment_method',
            [
                'is_enabled_for_casino',
            ]
        );
    }
}