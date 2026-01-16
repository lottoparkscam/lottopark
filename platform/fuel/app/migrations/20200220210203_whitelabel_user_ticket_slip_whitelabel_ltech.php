<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 *
 */
final class Whitelabel_User_Ticket_Slip_Whitelabel_Ltech extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_user_ticket_slip', [
            'whitelabel_ltech_id' => [
                'type' => 'tinyint',
                'constraint' => 3,
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
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_user_ticket_slip', 'whitelabel_ltech_id');
    }
}
