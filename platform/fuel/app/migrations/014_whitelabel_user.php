<?php

namespace Fuel\Migrations;

class Whitelabel_User
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_user',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'token' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'language_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_active' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_confirmed' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'email' => ['type' => 'varchar', 'constraint' => 254],
                'hash' => ['type' => 'varchar', 'constraint' => 128],
                'salt' => ['type' => 'varchar', 'constraint' => 128],
                'activation_hash' => ['type' => 'varchar', 'constraint' => 64, 'null' => true, 'default' => null],
                'activation_valid' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'resend_hash' => ['type' => 'varchar', 'constraint' => 64, 'null' => true, 'default' => null],
                'resend_last' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'lost_hash' => ['type' => 'varchar', 'constraint' => 64, 'null' => true, 'default' => null],
                'lost_last' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'name' => ['type' => 'varchar', 'constraint' => 100],
                'surname' => ['type' => 'varchar', 'constraint' => 100],
                'balance' => ['type' => 'decimal', 'constraint' => [9,2], 'unsigned' => true],
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
                'gender' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'national_id' => ['type' => 'varchar', 'constraint' => 30],
                'date_register' => ['type' => 'datetime'],
                'register_ip' => ['type' => 'varchar', 'constraint' => 45],
                'last_ip' => ['type' => 'varchar', 'constraint' => 45],
                'register_country' => ['type' => 'varchar', 'constraint' => 2, 'null' => true, 'default' => null],
                'last_country' => ['type' => 'varchar', 'constraint' => 2, 'null' => true, 'default' => null],
                'last_active' => ['type' => 'datetime'],
                'last_update' => ['type' => 'datetime'],
                'first_purchase' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'is_deleted' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
                'date_delete' => ['type' => 'datetime', 'null' => true, 'default' => null],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_user_currency_id_currency_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_user_language_id_language_idfx',
                    'key' => 'language_id',
                    'reference' => [
                        'table' => 'language',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_user_whitelabel_id_whitelabel_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_user', 'whitelabel_id', 'whitelabel_user_whitelabel_id_whitelabel_idfx_idx');
        \DBUtil::create_index('whitelabel_user', 'language_id', 'whitelabel_user_language_id_language_idfx_idx');
        \DBUtil::create_index('whitelabel_user', 'currency_id', 'whitelabel_user_currency_id_currency_idfx_idx');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'id', 'email', 'language_id', 'country', 'name', 'surname'], 'whitelabel_user_w_id_id_email_language_country_name_surname_idmx');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'is_deleted', 'email', 'hash'], 'whitelabel_user_whitelabel_id_email_hash_idmx');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_confirmed'], 'whitelabel_user_w_id_confirmed');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_active'], 'whitelabel_user_w_id_active');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_active', 'last_update'], 'whitelabel_user_w_id_last_update_idx');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_active', 'last_active'], 'whitelabel_user_w_id_last_active_idx');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_active', 'date_register'], 'whitelabel_user_w_id_date_register_idx');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_active', 'balance'], 'whitelabel_user_w_id_balance_idx');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_active', 'name', 'surname'], 'whitelabel_user_w_id_name_surname_idx');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_active', 'id'], 'whitelabel_user_w_id_idx');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_active', 'date_register', 'language_id', 'country'], 'whitelabel_user_w_id_date_register_language_country_idmx');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_active', 'first_purchase', 'language_id', 'country'], 'whitelabel_user_w_id_first_purchase_language_country_idmx');
        \DBUtil::create_index('whitelabel_user', ['whitelabel_id', 'token'], 'whitelabel_user_w_id_token_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_user', 'whitelabel_user_currency_id_currency_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user', 'whitelabel_user_language_id_language_idfx');
        \DBUtil::drop_foreign_key('whitelabel_user', 'whitelabel_user_whitelabel_id_whitelabel_idfx');

        \DBUtil::drop_table('whitelabel_user');
    }
}
