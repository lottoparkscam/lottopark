<?php

namespace Fuel\Migrations;

/**
 * Description of Bonus
 *
 */
class Bonus
{
    public function up()
    {
        \DBUtil::create_table(
            'bonus',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'varchar', 'constraint' => 40]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );
    }

    public function down()
    {
        \DBUtil::drop_table('bonus');
    }
}
