<?php

namespace Fuel\Migrations;

class Lottery_Source
{
    public function up()
    {
        \DBUtil::create_table(
            'lottery_source',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'name' => ['type' => 'varchar', 'constraint' => 45],
                'website' => ['type' => 'varchar', 'constraint' => 45]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );

        \DBUtil::create_index('lottery_source', 'lottery_id', 'lottery_source_lotter_id_lottery_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_table('lottery_source');
    }
}
