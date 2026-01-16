<?php

namespace Fuel\Migrations;

use Fuel\Core\DB;
use Fuel\Core\DBUtil;

final class Modify_Draw_Hours extends \Database_Migration_Graceful
{
    public function before()
    {
        try {
            DB::start_transaction();
            $this->translateDrawHours();
        } catch (\Throwable $e) {
            DB::rollback_transaction();
            throw $e;
        }
    }

    protected function up_gracefully(): void
    {
        DBUtil::drop_fields('lottery', [
            'draw_days',
            'draw_hour_local'
        ]);
        DBUtil::modify_fields('lottery', [
            'draw_hours' => [
                'name' => 'draw_dates',
                'type' => 'json',
                'null' => false
            ],
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::add_fields('lottery', [
            'draw_days' => [
                'type' => 'varchar',
                'constraint' => 15
            ],
            'draw_hour_local' => [
                'type' => 'time'
            ]
        ]);
        DBUtil::modify_fields('lottery', [
            'draw_dates' => [
                'name' => 'draw_hours',
                'type' => 'json',
                'null' => true
            ],
        ]);
    }

    protected function translateDrawHours()
    {
        $lotteries = \Model_Lottery::find_all();
        if (empty($lotteries)) {
            return;
        }

        /** @var \Model_Lottery $lottery */
        foreach ($lotteries as $lottery) {
            if (!is_null($lottery['draw_hours'])) {
                continue;
            }
            $lottery->set([
                'draw_hours' => \Helpers_Time::generate_draw_days_json($lottery['draw_days'], $lottery['draw_hour_local']),
                'next_date_local' => explode(' ', $lottery->next_date_local)[0] . " {$lottery['draw_hour_local']}",
                'last_date_local' => explode(' ', $lottery->last_date_local)[0] . " {$lottery['draw_hour_local']}",
            ]);
            $lottery->save();
        }
    }
}