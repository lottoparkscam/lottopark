<?php

namespace Fuel\Migrations;

class Create_whitelabel_refer_statistics
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_refer_statistics',
            [
            'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'refer' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'clicks' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'unique_clicks' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'registrations' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'free_tickets' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
        ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
            [
                'constraint' => 'whitelabel_refer_statistics_whitelabel_id_idfx',
                'key' => 'whitelabel_id',
                'reference' => [
                    'table' => 'whitelabel',
                    'column' => 'id'
                ]
            ]
        ]
        );
        
        \DBUtil::create_index('whitelabel_refer_statistics', 'whitelabel_id', 'whitelabel_refer_statistics_whitelabel_id_idfx');
        \DBUtil::create_index('whitelabel_refer_statistics', 'refer', 'whitelabel_refer_statistics_refer_idx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_refer_statistics', 'whitelabel_refer_statistics_whitelabel_id_idfx');
        
        \DBUtil::drop_table('whitelabel_refer_statistics');
    }
}
