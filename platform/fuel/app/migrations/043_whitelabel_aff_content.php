<?php

namespace Fuel\Migrations;

class Whitelabel_Aff_Content
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_aff_content',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_aff_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'content' => ['type' => 'varchar', 'constraint' => 100,],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_aff_content_wa_id_wa_idfx',
                    'key' => 'whitelabel_aff_id',
                    'reference' => [
                        'table' => 'whitelabel_aff',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('whitelabel_aff_content', 'whitelabel_aff_id', 'whitelabel_aff_content_wa_id_wa_idfx_idx');
        \DBUtil::create_index('whitelabel_aff_content', ['whitelabel_aff_id', 'content'], 'whitelabel_aff_content_wa_id_content_idmx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_aff_content', 'whitelabel_aff_content_wa_id_wa_idfx');

        \DBUtil::drop_table('whitelabel_aff_content');
    }
}
