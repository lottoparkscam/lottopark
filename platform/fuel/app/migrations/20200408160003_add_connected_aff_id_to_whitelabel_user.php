<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 *
 */
final class Add_connected_aff_id_to_whitelabel_user extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    public function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_user', [
            'connected_aff_id' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
                'default' => null
            ]
        ]);
    }

    /**
     *
     * @return void
     */
    public function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_user', [
            'connected_aff_id'
        ]);
    }
}
