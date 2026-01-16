<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 *
 */
final class Whitelabel_Uts_Whitelabel_Ltech_Id_Foreign extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_foreign_key('whitelabel_user_ticket_slip', [
            'constraint' => 'whitelabel_user_ticket_slip_wl_ltech_id_idpx',
            'key' => 'whitelabel_ltech_id',
            'reference' => [
                'table' => 'whitelabel_ltech',
                'column' => 'id'
            ],
            'on_update' => 'NO ACTION',
            'on_delete' => 'SET NULL'
        ]);
    }

    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_foreign_key(
            'whitelabel_user_ticket_slip',
            'whitelabel_user_ticket_slip_wl_ltech_id_idpx'
        );
    }
}
