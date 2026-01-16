<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Crm_Log_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            'crm_log',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'date' => ['type' => 'datetime'],
                'admin_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'module_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'message' => ['type' => 'varchar', 'constraint' => 255],
                'data' => ['type' => 'text']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key('crm_log', 'admin_user_id'),
                Helper_Migration::generate_foreign_key('crm_log', 'module_id'),
                Helper_Migration::generate_foreign_key('crm_log', 'whitelabel_id')
            ]
        );

        DBUtil::create_index(
            'crm_log',
            'module_id',
            'crm_log_module_id_idx'
        );

        DBUtil::create_index(
            'crm_log',
            'whitelabel_id',
            'crm_log_whitelabel_id_idx'
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('crm_log');
    }
}