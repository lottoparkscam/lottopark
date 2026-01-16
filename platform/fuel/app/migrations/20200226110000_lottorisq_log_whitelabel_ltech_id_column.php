<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 *
 */
final class Lottorisq_Log_Whitelabel_Ltech_Id_Column extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('lottorisq_log', [
            'whitelabel_ltech_id' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => true,
                'default' => null
            ]
        ]);

        DBUtil::add_foreign_key('lottorisq_log', [
            'constraint' => 'lottorisq_log_wl_ltech_id_idpx',
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
        \DBUtil::drop_foreign_key('lottorisq_log', 'lottorisq_log_wl_ltech_id_idpx');
        \DBUtil::drop_fields('lottorisq_log', 'whitelabel_ltech_id');
    }
}
