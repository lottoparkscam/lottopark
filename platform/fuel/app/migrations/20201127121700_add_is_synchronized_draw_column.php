<?php

namespace Fuel\Migrations;

use Fuel\Core\DB;
use Fuel\Core\DBUtil;

final class Add_is_synchronized_draw_column extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('raffle_draw', [
            'is_synchronized' => ['type' => 'boolean', 'default' => false, 'after' => 'draw_no'],
        ]);

        $query = DB::update('raffle_draw');
        $query->value('is_synchronized', true);
        $query->execute();
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('raffle_draw', [
            'is_synchronized',
        ]);
    }
}
