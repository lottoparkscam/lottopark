<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Order_To_Slot_Game extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'slot_game',
            [
                'order' => [
                    'type' => 'tinyint',
                    'constraint' => 3,
                    'default' => 0,
                    'unsigned' => true,
                    'after' => 'is_mobile'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'slot_game',
            [
                'order',
            ]
        );
    }
}