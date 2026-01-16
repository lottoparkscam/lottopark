<?php

namespace Fuel\Migrations;

class Imvalap_Job
{
    public function up()
    {
        \DBUtil::create_table(
            'imvalap_job',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'game_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'jobid' => ['type' => 'varchar', 'constraint' => 25],
                'status' => ['type' => 'varchar', 'constraint' => 3],
                'bets' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
                'bets_reserved' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );

        \DBUtil::create_index('imvalap_job', 'jobid', 'imvalap_job_jobid_idx', 'UNIQUE');
        \DBUtil::create_index('imvalap_job', 'status', 'imvalap_job_status_idx');

    }

    public function down()
    {
        \DBUtil::drop_table('imvalap_job');
    }
}
