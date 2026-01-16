<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DB;
use Fuel\Core\DBUtil;

final class Add_Whitelabel_User_Aff_Id_In_Whitelabel_Aff_Commission extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DB::query('SET FOREIGN_KEY_CHECKS=0;')->execute();
        DBUtil::add_fields('whitelabel_aff_commission', [
            'whitelabel_user_aff_id' => [
                'type' => 'int', 
                'constraint' => 10, 
                'unsigned' => true, 
                'null' => true,
                'after' => 'whitelabel_aff_id'
            ]
        ]);

        try {
            DB::start_transaction();
            $queryString = 'UPDATE whitelabel_aff_commission wac
                LEFT JOIN whitelabel_transaction wt on wt.id = wac.whitelabel_transaction_id
                LEFT JOIN whitelabel_user_aff wua on wua.whitelabel_user_id = wt.whitelabel_user_id
                SET wac.whitelabel_user_aff_id = wua.whitelabel_aff_id
                WHERE wac.whitelabel_user_aff_id IS NULL AND wua.whitelabel_aff_id IS NOT NULL;';

            DB::query($queryString, DB::UPDATE)->execute();

        } catch (\Throwable $e) {
            DB::rollback_transaction();
            throw $e;
        }

        DBUtil::add_foreign_key(
            'whitelabel_aff_commission', [
                'constraint' => 'whitelabel_aff_commission_whitelabel_user_aff_id_foreign',
                'key' => 'whitelabel_user_aff_id',
                'reference' => [
                    'table' => 'whitelabel_aff',
                    'column' => 'id'
                ],
                'on_update' => 'NO ACTION',
                'on_delete' => 'CASCADE'
            ]
        );

        DB::query('SET FOREIGN_KEY_CHECKS=1;')->execute();
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_foreign_key('whitelabel_aff_commission', 'whitelabel_aff_commission_whitelabel_user_aff_id_foreign');
        DBUtil::drop_fields('whitelabel_aff_commission', [
            'whitelabel_user_aff_id',
        ]);
    }
}
