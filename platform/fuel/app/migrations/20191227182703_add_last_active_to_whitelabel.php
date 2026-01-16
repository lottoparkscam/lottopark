<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Last_Active_To_Whitelabel extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel', [
            'last_active' => [
                'type' => 'datetime',
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
        DBUtil::drop_fields('whitelabel', [
            
        ]);
    }
}
