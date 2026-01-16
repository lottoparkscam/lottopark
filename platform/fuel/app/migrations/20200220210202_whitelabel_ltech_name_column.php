<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 *
 */
final class Whitelabel_Ltech_Name_Column extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_ltech', [
            'name' => [
                'type' => 'varchar',
                'constraint' => 80,
                'null' => true
            ]
        ]);
    }

    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_ltech', 'name');
    }
}
