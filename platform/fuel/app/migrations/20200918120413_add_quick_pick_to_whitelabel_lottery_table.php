<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Quick_Pick_To_Whitelabel_Lottery_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_lottery', [
            'quick_pick_lines' => [
                'type' => 'int',
                'default' => 0,
                'after' => 'min_lines'
            ]
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_lottery', [
            'quick_pick_lines',
        ]);
    }
}