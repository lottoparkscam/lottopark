<?php

namespace Fuel\Migrations;

class Whitelabel
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'language_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'name' => ['type' => 'varchar', 'constraint' => 50],
                'domain' => ['type' => 'varchar', 'constraint' => 200],
                'type' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'null' => true, 'default' => 1],
                'user_activation_type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'aff_activation_type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'aff_auto_accept' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 1],
                'aff_payout_type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 1],
                'aff_lead_auto_accept' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 1],
                'aff_ref_lifetime' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
                'aff_hide_ticket_and_payment_cost' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
                'aff_hide_amount' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
                'aff_hide_income' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
                'aff_enable_sign_ups' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
                'max_payout' => ['type' => 'decimal', 'constraint' => [11,2], 'unsigned' => true],
                'register_name_surname' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'null' => true, 'default' => 0],
                'register_phone' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'null' => true, 'default' => 0],
                'username' => ['type' => 'varchar', 'constraint' => 100],
                'hash' => ['type' => 'varchar', 'constraint' => 128],
                'salt' => ['type' => 'varchar', 'constraint' => 128],
                'email' => ['type' => 'varchar', 'constraint' => 254],
                'realname' => ['type' => 'varchar', 'constraint' => 100],
                'timezone' => ['type' => 'varchar', 'constraint' => 40],
                'prefix' => ['type' => 'varchar', 'constraint' => 2],
                'def_commission_type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'def_commission_value' => ['type' => 'decimal', 'constraint' => [8,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'def_commission_value_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'def_commission_type_2' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'def_commission_value_2' => ['type' => 'decimal', 'constraint' => [8,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'def_commission_value_2_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'def_ftp_commission_value' => ['type' => 'decimal', 'constraint' => [8,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'def_ftp_commission_value_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'def_ftp_commission_value_2' => ['type' => 'decimal', 'constraint' => [8,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'def_ftp_commission_value_2_manager' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true, 'null' => true, 'default' => null],
                'analytics' => ['type' => 'varchar', 'constraint' => 45, 'null' => true, 'default' => null],
                'ceg_seal_id' => ['type' => 'varchar', 'constraint' => 45, 'null' => true, 'default' => null],
                'default_site_currency' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'fb_pixel' => ['type' => 'varchar', 'constraint' => 25, 'null' => true, 'default' => null],
                'fb_pixel_match' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => 0],
                'margin' => ['type' => 'decimal', 'constraint' => [5,2], 'unsigned' => true, 'null' => true, 'default' => 10.00],
                'prepaid' => ['type' => 'decimal', 'constraint' => [10,2], 'default' => 0.00],
                'manager_site_currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 2],
                'max_order_count' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 20],
                'theme' => ['type' => 'varchar', 'constraint' => 50,  'default' => ''],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_dc_id_dc_idfx',
                    'key' => 'default_site_currency',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_language_id_language_idfx',
                    'key' => 'language_id',
                    'reference' => [
                        'table' => 'language',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'NO ACTION'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel', 'domain', 'whitelabel_domain_idx');
        \DBUtil::create_index('whitelabel', 'language_id', 'whitelabel_language_id_language_idfx_idx');
        \DBUtil::create_index('whitelabel', 'name', 'whitelabel_name_idx');
        \DBUtil::create_index('whitelabel', 'default_site_currency', 'whitelabel_dc_id_dc_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel', 'whitelabel_dc_id_dc_idfx');
        \DBUtil::drop_foreign_key('whitelabel', 'whitelabel_language_id_language_idfx');

        \DBUtil::drop_table('whitelabel');
    }
}
