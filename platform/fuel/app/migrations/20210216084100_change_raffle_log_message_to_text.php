<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Change_raffle_log_message_to_text extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            'raffle_log',
            [
                'message' => [
                    'type' => 'text',
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            'raffle_log',
            [
                'message' => [
                    'type' => 'varchar',
                    'constraint'=> 255
                ],
            ]
        );
    }
}
