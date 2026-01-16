<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DB;
use Fuel\Core\DBUtil;

final class Change_prize_in_kind_type_to_name extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        # rename type => name
        DBUtil::modify_fields('raffle_rule_tier_in_kind_prizes', [
            'type'     => ['name' => 'name', 'type' => 'varchar', 'constraint' => 50, 'null' => false],
        ]);

        # create new type
        DBUtil::add_fields('raffle_rule_tier_in_kind_prizes', [
            'type'     => ['type' => 'varchar', 'constraint' => 15, 'null' => false, 'after' => 'slug'],
            'config'     => ['type' => 'json', 'null' => true],
        ]);

        DB::update('raffle_rule_tier_in_kind_prizes')->value('type', 'prize-in-kind')->execute();
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('raffle_rule_tier_in_kind_prizes', ['type', 'config']);

        DBUtil::modify_fields('raffle_rule_tier_in_kind_prizes', [
            'name'     => ['name' => 'type', 'type' => 'varchar', 'constraint' => 100, 'null' => true],
        ]);
    }
}
