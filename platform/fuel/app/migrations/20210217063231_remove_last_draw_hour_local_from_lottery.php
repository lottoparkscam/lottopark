<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Remove_Last_Draw_Hour_Local_From_Lottery extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::drop_fields('lottery', 'last_draw_hour_local');
    }

    protected function down_gracefully(): void
    {
        DBUtil::add_fields(
            'lottery',
            [
                'last_draw_hour_local' => [
                    'type' => 'time',
                    'null' => true,
                    'default' => null,
                    'after' => 'draw_dates'
                ],
            ]
        );
    }
}
