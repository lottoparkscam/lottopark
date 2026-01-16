<?php

namespace Fuel\Migrations;

class Whitelabel_Aff_Click
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_aff_click',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_aff_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'date' => ['type' => 'datetime'],
                'whitelabel_aff_medium_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_aff_campaign_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_aff_content_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'all' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
                'unique' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_aff_click_wa_campaign_idfx',
                    'key' => 'whitelabel_aff_campaign_id',
                    'reference' => [
                        'table' => 'whitelabel_aff_campaign',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_aff_click_wa_content_idfx',
                    'key' => 'whitelabel_aff_content_id',
                    'reference' => [
                        'table' => 'whitelabel_aff_content',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_aff_click_wa_id_wa_idfx',
                    'key' => 'whitelabel_aff_id',
                    'reference' => [
                        'table' => 'whitelabel_aff',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_aff_click_wa_medium_idfx',
                    'key' => 'whitelabel_aff_medium_id',
                    'reference' => [
                        'table' => 'whitelabel_aff_medium',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_aff_click', 'whitelabel_aff_id', 'whitelabel_aff_click_wa_id_wa_idfx_idx');
        \DBUtil::create_index('whitelabel_aff_click', ['whitelabel_aff_id', 'date'], 'whitelabel_aff_click_wa_id_date_idmx');
        \DBUtil::create_index('whitelabel_aff_click', ['whitelabel_aff_id', 'whitelabel_aff_medium_id', 'whitelabel_aff_campaign_id', 'whitelabel_aff_content_id', 'date'], 'whitelabel_aff_click_wa_id_m_c_c_date_idmx');
        \DBUtil::create_index('whitelabel_aff_click', 'whitelabel_aff_campaign_id', 'whitelabel_aff_click_wa_campaign_idfx_idx');
        \DBUtil::create_index('whitelabel_aff_click', 'whitelabel_aff_content_id', 'whitelabel_aff_click_wa_content_idfx_idx');
        \DBUtil::create_index('whitelabel_aff_click', 'whitelabel_aff_medium_id', 'whitelabel_aff_click_wa_medium_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_aff_click', 'whitelabel_aff_click_wa_campaign_idfx');
        \DBUtil::drop_foreign_key('whitelabel_aff_click', 'whitelabel_aff_click_wa_content_idfx');
        \DBUtil::drop_foreign_key('whitelabel_aff_click', 'whitelabel_aff_click_wa_id_wa_idfx');
        \DBUtil::drop_foreign_key('whitelabel_aff_click', 'whitelabel_aff_click_wa_medium_idfx');

        \DBUtil::drop_table('whitelabel_aff_click');
    }
}
