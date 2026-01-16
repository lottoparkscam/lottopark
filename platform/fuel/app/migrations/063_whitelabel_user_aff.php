<?php

namespace Fuel\Migrations;

class Whitelabel_User_Aff
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_user_aff',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_aff_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_aff_medium_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_aff_campaign_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_aff_content_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'is_deleted' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
                'is_accepted' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'is_expired' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_user_aff_w_id_w_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_user_aff_wa_campaign_idfx',
                    'key' => 'whitelabel_aff_campaign_id',
                    'reference' => [
                        'table' => 'whitelabel_aff_campaign',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_user_aff_wa_content_idfx',
                    'key' => 'whitelabel_aff_content_id',
                    'reference' => [
                        'table' => 'whitelabel_aff_content',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_user_aff_wa_id_wa_idfx',
                    'key' => 'whitelabel_aff_id',
                    'reference' => [
                        'table' => 'whitelabel_aff',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_user_aff_wa_medium_idfx',
                    'key' => 'whitelabel_aff_medium_id',
                    'reference' => [
                        'table' => 'whitelabel_aff_medium',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_user_aff_wu_id_wu_idfx',
                    'key' => 'whitelabel_user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_user_aff', 'whitelabel_aff_id', 'whitelabel_user_aff_wa_id_wa_idfx_idx');
        \DBUtil::create_index('whitelabel_user_aff', 'whitelabel_aff_campaign_id', 'whitelabel_user_aff_wa_campaign_idfx_idx');
        \DBUtil::create_index('whitelabel_user_aff', 'whitelabel_aff_content_id', 'whitelabel_user_aff_wa_content_idfx_idx');
        \DBUtil::create_index('whitelabel_user_aff', 'whitelabel_aff_medium_id', 'whitelabel_user_aff_wa_medium_idfx_idx');
        \DBUtil::create_index('whitelabel_user_aff', ['whitelabel_user_id', 'whitelabel_aff_id', 'is_deleted', 'is_accepted'], 'whitelabel_user_aff_wu_id_wa_id_idmx');
        \DBUtil::create_index('whitelabel_user_aff', ['is_deleted', 'is_accepted', 'whitelabel_user_id', 'whitelabel_aff_id', 'whitelabel_aff_medium_id', 'whitelabel_aff_campaign_id', 'whitelabel_aff_content_id'], 'whitelabel_user_aff_wu_id_wa_id_m_c_c_idmx');
        \DBUtil::create_index('whitelabel_user_aff', 'whitelabel_id', 'whitelabel_user_aff_w_id_w_idfx_idx');
        \DBUtil::create_index('whitelabel_user_aff', 'whitelabel_user_id', 'whitelabel_user_aff_wu_id_wu_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_user_aff', 'whitelabel_user_aff_w_id_w_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_aff', 'whitelabel_user_aff_wa_campaign_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_aff', 'whitelabel_user_aff_wa_content_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_aff', 'whitelabel_user_aff_wa_id_wa_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_aff', 'whitelabel_user_aff_wa_medium_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user_aff', 'whitelabel_user_aff_wu_id_wu_idfx');

        \DBUtil::drop_table('whitelabel_user_aff');
    }
}
