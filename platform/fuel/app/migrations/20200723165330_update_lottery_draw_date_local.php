<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DB;
use Fuel\Core\DBUtil;

class Update_lottery_draw_date_local extends Database_Migration_Graceful
{
    public function up_gracefully(): void
    {
        set_time_limit(60);

        DBUtil::modify_fields('lottery_draw', [
            'date_local' => [
                'type' => 'datetime'
            ],
        ]);
        try {
            DB::start_transaction();
            DB::query('UPDATE `lottery_draw` ld 
left join lottery l
on l.id = ld.lottery_id
SET `date_local` = CONCAT(DATE(ld.`date_local`), " ", l.`draw_hour_local`) 
WHERE ld.id > 0;', DB::UPDATE)->execute(); // NOTE: ugly hack ld.id > 0 for safe mode
        } catch (\Throwable $e) {
            DB::rollback_transaction();
            throw $e;
        }
        DBUtil::drop_fields('lottery_draw', 'hour_local');
    }

    public function down_gracefully(): void
    {
        DBUtil::add_fields(
            'lottery_draw',
            [
                'hour_local' => [
                    'type' => 'time',
                    'null' => true,
                    'default' => null,
                    'after' => 'date_local'
                ],
            ]
        );
        try {
            DB::start_transaction();
            DB::query('UPDATE `lottery_draw` a SET `hour_local` = DATE_FORMAT(a.`date_local`, "%H:%i:%s") WHERE 1;', DB::UPDATE)->execute();
        } catch (\Throwable $e) {
            DB::rollback_transaction();
            throw $e;
        }
        DBUtil::modify_fields('lottery_draw', [
            'date_local' => [
                'type' => 'date'
            ],
        ]);
    }
}
