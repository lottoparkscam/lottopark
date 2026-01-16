<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 *
 */
final class Whitelabel_Ltech_Secret_Key_Column extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_ltech', [
            'secret' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => true,
                'default' => null
            ]
        ]);
    }

    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_ltech', 'secret');
    }
}
