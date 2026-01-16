<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DB;
use Fuel\Core\DBUtil;

class Alter_Raffle_Rule_Tier_Prize_Found_Percent_Column extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields('raffle_rule_tier', [
            'prize_fund_percent' => [
                'type' => 'decimal',
                'constraint' => [5, 2],
                'unsigned' => true
            ],
        ]);
    }

    /**
     * Revert migration.
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DB::delete('raffle_rule_tier')
            ->where('prize_fund_percent', '>', 99.9)
            ->execute();
        DBUtil::modify_fields('raffle_rule_tier', [
            'prize_fund_percent' => [
                'type' => 'decimal',
                'constraint' => [4, 2],
                'unsigned' => true
            ],
        ]);
    }
}
