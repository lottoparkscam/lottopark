<?php

namespace Fuel\Migrations;

class Whitelabel_Aff_Group
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_aff_group',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'name' => ['type' => 'varchar', 'constraint' => 40],
                'commission_type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'commission_value' => ['type' => 'decimal', 'constraint' => [8, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'commission_value_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'commission_type_2' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'commission_value_2' => ['type' => 'decimal', 'constraint' => [8, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'commission_value_2_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'ftp_commission_value' => ['type' => 'decimal', 'constraint' => [8, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'ftp_commission_value_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'ftp_commission_value_2' => ['type' => 'decimal', 'constraint' => [8, 2], 'unsigned' => true, 'null' => true, 'default' => null],
                'ftp_commission_value_2_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'null' => true, 'default' => null]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_aff_group_w_id_w_idfx',
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

        \DBUtil::create_index('whitelabel_aff_group', 'whitelabel_id', 'whitelabel_aff_group_w_id_w_idfx_idx');
        \DBUtil::create_index('whitelabel_aff_group', ['whitelabel_id', 'name'], 'whitelabel_aff_group_w_id_name_idmx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_aff_group', 'whitelabel_aff_group_w_id_w_idfx');

        \DBUtil::drop_table('whitelabel_aff_group');
    }
}
