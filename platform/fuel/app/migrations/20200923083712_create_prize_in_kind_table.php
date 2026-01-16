<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Create_prize_in_kind_table extends \Database_Migration_Graceful
{
    public function up_gracefully(): void
    {
        DBUtil::create_table(
            'raffle_rule_tier_in_kind_prizes',
            [
            'id'       => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'type'     => ['type' => 'varchar', 'constraint' => 100, 'null' => false],
            'slug'     => ['type' => 'varchar', 'constraint' => 100, 'null' => false],
            'per_user' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true],
        ],
            ['id']
        );
    }

    public function down_gracefully(): void
    {
        DBUtil::drop_table('raffle_rule_tier_in_kind_prizes');
    }
}
