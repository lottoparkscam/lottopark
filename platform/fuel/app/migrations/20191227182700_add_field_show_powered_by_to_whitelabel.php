<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 *
 */
final class Add_field_Show_Powered_By_To_Whitelabel extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel', [
            'show_powered_by' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 0
            ]
        ]);
    }

    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel', [
            'show_powered_by'
        ]);
    }
}
