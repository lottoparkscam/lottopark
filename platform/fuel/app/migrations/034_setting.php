<?php

namespace Fuel\Migrations;

class Setting
{
    public function up()
    {
        \DBUtil::create_table(
            'setting',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'varchar', 'constraint' => 45],
                'value' => ['type' => 'text']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );

        // TODO: check name(8)
//        DB::query('ALTER TABLE `setting` ADD KEY `setting_name_idx` (`name`(8))')->execute();
        \DBUtil::create_index('setting', ['name' => '(8)'], 'setting_name_idx');

    }

    public function down()
    {
        \DBUtil::drop_table('setting');
    }
}
