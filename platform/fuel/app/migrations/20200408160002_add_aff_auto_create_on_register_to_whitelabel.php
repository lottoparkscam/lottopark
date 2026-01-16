<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_aff_auto_create_on_register_to_whitelabel extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    public function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel', [
            'aff_auto_create_on_register' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 0,
                'after' => 'aff_enable_sign_ups'
            ]
        ]);
    }

    /**
     *
     * @return void
     */
    public function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel', [
            'aff_auto_create_on_register'
        ]);
    }
}
