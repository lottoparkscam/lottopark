<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_user_registration_through_ref_only_to_whitelabel extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    public function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel', [
            'user_registration_through_ref_only' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 0,
                'after' => 'user_activation_type'
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
            'user_registration_through_ref_only'
        ]);
    }
}
