<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Is_Margin_Calculation_Enabled_Flag_To_Whitelabel_Raffle_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_raffle', [
            'is_margin_calculation_enabled' => [
                'type'          => 'tinyint',
                'constraint'    => 1,
                'unsigned'      => true,
                'default'       => 1,
                'after'         => 'is_enabled'
            ]
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_raffle', [
            'is_margin_calculation_enabled'
        ]);
    }
}