<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Create_whitelabel_user_popup_queue extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            'whitelabel_user_popup_queue',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'title' => ['type' => 'varchar', 'constraint' => 255],
                'content' => ['type' => 'text'],
                'created_at' => ['type' => 'datetime']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_user_popup_queue_whitelabel_id_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'whitelabel_user_popup_queue_whitelabel_user_id_idfx',
                    'key' => 'whitelabel_user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ]
                ],
            ]
        );
        
        DBUtil::create_index('whitelabel_user_popup_queue', 'whitelabel_id', 'whitelabel_user_popup_queue_whitelabel_id_idfx');
        DBUtil::create_index('whitelabel_user_popup_queue', 'whitelabel_id', 'whitelabel_user_popup_queue_whitelabel_id_user_idfx');
    }
    
    protected function down_gracefully(): void
    {
        DBUtil::drop_foreign_key('whitelabel_user_popup_queue', 'whitelabel_user_popup_queue_whitelabel_id_idfx');
        DBUtil::drop_foreign_key('whitelabel_user_popup_queue', 'whitelabel_user_popup_queue_whitelabel_user_id_idfx');
        
        DBUtil::drop_table('whitelabel_user_popup_queue');
    }
}
