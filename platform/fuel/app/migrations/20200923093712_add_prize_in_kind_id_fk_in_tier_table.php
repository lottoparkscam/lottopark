<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;
use Helper_Migration;

class Add_prize_in_kind_id_fk_in_tier_table extends \Database_Migration_Graceful
{
    public function up_gracefully(): void
    {
        DBUtil::add_fields('raffle_rule_tier', [
            'lottery_rule_tier_in_kind_prize_id' => [
                'type'       => 'int',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'unique'     => true,
                'after'      => 'prize_type'
            ],
        ]);

        # note: Helper_Migration is not working
        DBUtil::add_foreign_key('raffle_rule_tier', [
            'constraint' => 'raffle_rule_tier_lottery_rule_tier_in_kind_prize_id_idfx',
            'key' => 'lottery_rule_tier_in_kind_prize_id',
            'reference' => [
                'table' => 'raffle_rule_tier_in_kind_prizes',
                'column' => 'id'
            ],
            'on_update' => 'NO ACTION',
            'on_delete' => 'CASCADE'
        ]);
    }

    public function down_gracefully(): void
    {
        DBUtil::drop_foreign_key('raffle_rule_tier', 'raffle_rule_tier_lottery_rule_tier_in_kind_prize_id_idfx');

        DBUtil::drop_fields('raffle_rule_tier', ['lottery_rule_tier_in_kind_prize_id']);
    }
}
