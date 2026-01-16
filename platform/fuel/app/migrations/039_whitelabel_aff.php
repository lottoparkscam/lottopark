<?php

namespace Fuel\Migrations;

class Whitelabel_Aff
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_aff',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_aff_parent_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'language_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'whitelabel_aff_group_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_aff_withdrawal_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'is_active' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_confirmed' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_accepted' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'login' => ['type' => 'varchar', 'constraint' => 30],
                'email' => ['type' => 'varchar', 'constraint' => 254],
                'token' => ['type' => 'varchar', 'constraint' => 10],
                'hash' => ['type' => 'varchar', 'constraint' => 128],
                'salt' => ['type' => 'varchar', 'constraint' => 128],
                'company' => ['type' => 'varchar', 'constraint' => 100, 'null' => true, 'default' => null],
                'name' => ['type' => 'varchar', 'constraint' => 100, 'null' => true, 'default' => null],
                'surname' => ['type' => 'varchar', 'constraint' => 100, 'null' => true, 'default' => null],
                'address1' => ['type' => 'varchar', 'constraint' => 100],
                'address2' => ['type' => 'varchar', 'constraint' => 100],
                'city' => ['type' => 'varchar', 'constraint' => 100],
                'country' => ['type' => 'varchar', 'constraint' => 2],
                'state' => ['type' => 'varchar', 'constraint' => 100],
                'zip' => ['type' => 'varchar', 'constraint' => 20],
                'phone_country' => ['type' => 'varchar', 'constraint' => 3],
                'phone' => ['type' => 'varchar', 'constraint' => 100],
                'birthdate' => ['type' => 'date', 'null' => true, 'default' => null],
                'timezone' => ['type' => 'varchar', 'constraint' => 40],
                'withdrawal_data' => ['type' => 'text', 'null' => true],
                'analytics' => ['type' => 'varchar', 'constraint' => 45, 'null' => true, 'default' => null],
                'date_created' => ['type' => 'datetime'],
                'last_ip' => ['type' => 'varchar', 'constraint' => 45, 'null' => true, 'default' => null],
                'last_country' => ['type' => 'varchar', 'constraint' => 2, 'null' => true, 'default' => null],
                'last_active' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'is_deleted' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
                'date_delete' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'aff_lead_lifetime' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
                'is_show_name' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
                'hide_lead_id' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 1],
                'hide_transaction_id' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 1],
                'activation_hash' => ['type' => 'varchar', 'constraint' => 64, 'null' => true, 'default' => null],
                'activation_valid' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'resend_hash' => ['type' => 'varchar', 'constraint' => 64, 'null' => true, 'default' => null],
                'resend_last' => ['type' => 'datetime', 'null' => true, 'default' => null],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_aff_c_id_c_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'NO ACTION'
                ],
                [
                    'constraint' => 'whitelabel_aff_l_id_l_idfx',
                    'key' => 'language_id',
                    'reference' => [
                        'table' => 'language',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'NO ACTION'
                ],
                [
                    'constraint' => 'whitelabel_aff_w_id_w_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_aff_wag_id_wag_idfx',
                    'key' => 'whitelabel_aff_group_id',
                    'reference' => [
                        'table' => 'whitelabel_aff_group',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_aff_waw_id_waw_idfx',
                    'key' => 'whitelabel_aff_withdrawal_id',
                    'reference' => [
                        'table' => 'whitelabel_aff_withdrawal',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_aff', 'whitelabel_id', 'whitelabel_aff_w_id_w_idfx_idx');
        \DBUtil::create_index('whitelabel_aff', 'language_id', 'whitelabel_aff_l_id_l_idfx_idx');
        \DBUtil::create_index('whitelabel_aff', 'currency_id', 'whitelabel_aff_c_id_c_idfx_idx');
        \DBUtil::create_index('whitelabel_aff', ['whitelabel_id', 'is_deleted', 'is_accepted', 'is_active', 'is_confirmed'], 'whitelabel_aff_w_id_is_d_is_a_is_a_is_c_idmx');
        \DBUtil::create_index('whitelabel_aff', 'whitelabel_aff_group_id', 'whitelabel_aff_wag_id_wag_idfx_idx');
        \DBUtil::create_index('whitelabel_aff', ['whitelabel_id', 'is_deleted', 'login', 'hash'], 'whitelabel_aff_w_id_is_d_login_hash_idmx');
        \DBUtil::create_index('whitelabel_aff', ['whitelabel_id', 'is_deleted', 'name', 'surname'], 'whitelabel_aff_w_id_is_d_name_idmx');
        \DBUtil::create_index('whitelabel_aff', ['whitelabel_id', 'is_deleted', 'last_active'], 'whitelabel_aff_w_id_is_d_last_active_idmx');
        \DBUtil::create_index('whitelabel_aff', ['whitelabel_id', 'is_deleted', 'date_delete'], 'whitelabel_aff_w_id_is_d_date_delete_idmx');
        \DBUtil::create_index('whitelabel_aff', ['whitelabel_id', 'is_deleted', 'email'], 'whitelabel_aff_w_id_is_d_email_idmx');
        \DBUtil::create_index('whitelabel_aff', ['whitelabel_id', 'is_deleted', 'token'], 'whitelabel_aff_w_id_is_d_token_idmx');
        \DBUtil::create_index('whitelabel_aff', 'whitelabel_aff_withdrawal_id', 'whitelabel_aff_waw_id_waw_idfx_idx');
        \DBUtil::create_index('whitelabel_aff', 'whitelabel_aff_parent_id', 'whitelabel_aff_wap_id_wap_idpx_idx');
        \DBUtil::create_index('whitelabel_aff', ['whitelabel_id', 'name', 'surname', 'login'], 'whitelabel_aff_w_id_name_surname_login_idmx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_aff', 'whitelabel_aff_c_id_c_idfx');
        \DBUtil::drop_foreign_key('whitelabel_aff', 'whitelabel_aff_l_id_l_idfx');
        \DBUtil::drop_foreign_key('whitelabel_aff', 'whitelabel_aff_w_id_w_idfx');
        \DBUtil::drop_foreign_key('whitelabel_aff', 'whitelabel_aff_wag_id_wag_idfx');
        \DBUtil::drop_foreign_key('whitelabel_aff', 'whitelabel_aff_waw_id_waw_idfx');

        \DBUtil::drop_table('whitelabel_aff');
    }
}
