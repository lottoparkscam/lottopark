<?php

namespace Fuel\Migrations;

/**
 * Description of Whitelabel_Payment_Method_Customize
 */
final class Whitelabel_Payment_Method_Customize
{
    /**
     *
     * @return void
     */
    public function up(): void
    {
        \DBUtil::create_table(
            'whitelabel_payment_method_customize',
            [
                'id' => [
                    'type' => 'int',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true
                ],
                'whitelabel_payment_method_id' => [
                    'type' => 'int',
                    'constraint' => 10,
                    'unsigned' => true
                ],
                'whitelabel_language_id' => [
                    'type' => 'int',
                    'constraint' => 10,
                    'unsigned' => true
                ],
                'title' => [
                    'type' => 'varchar',
                    'constraint' => 255,
                    'default' => '',
                ],
                'title_for_mobile' => [
                    'type' => 'varchar',
                    'constraint' => 255,
                    'default' => '',
                ],
                'description' => [
                    'type' => 'text',
                    'null' => true,
                    'default' => null,
                ],
                'additional_failure_text' => [
                    'type' => 'text',
                    'null' => true,
                    'default' => null,
                ]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'wpmc_whitelabel_payment_method_idfx',
                    'key' => 'whitelabel_payment_method_id',
                    'reference' => [
                        'table' => 'whitelabel_payment_method',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'wpmc_whitelabel_language_idfx',
                    'key' => 'whitelabel_language_id',
                    'reference' => [
                        'table' => 'whitelabel_language',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index(
            'whitelabel_payment_method_customize',
            'whitelabel_payment_method_id',
            'wpmc_whitelabel_payment_method_idfx'
        );
        \DBUtil::create_index(
            'whitelabel_payment_method_customize',
            'whitelabel_language_id',
            'wpmc_whitelabel_language_idfx'
        );
    }
    
    /**
     *
     * @return void
     */
    public function down(): void
    {
        \DBUtil::drop_foreign_key(
            'whitelabel_payment_method_customize',
            'wpmc_whitelabel_payment_method_idfx'
        );
        \DBUtil::drop_foreign_key(
            'whitelabel_payment_method_customize',
            'wpmc_whitelabel_language_idfx'
        );

        \DBUtil::drop_table('whitelabel_payment_method_customize');
    }
}
