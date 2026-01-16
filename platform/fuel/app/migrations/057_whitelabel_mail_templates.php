<?php

namespace Fuel\Migrations;

class Whitelabel_Mail_Templates
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_mail_templates',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'mail_template_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'title' => ['type' => 'varchar', 'constraint' => 100],
                'content' => ['type' => 'text', 'null' => true],
                'additional_translates' => ['type' => 'text', 'null' => true],
                'lang' => ['type' => 'varchar', 'constraint' => 5],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_mail_templates_mail_template_id',
                    'key' => 'mail_template_id',
                    'reference' => [
                        'table' => 'mail_templates',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_mail_templates_whitelabel_id',
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

        \DBUtil::create_index('whitelabel_mail_templates', 'whitelabel_id', 'whitelabel_mail_templates_whitelabel_id');
        \DBUtil::create_index('whitelabel_mail_templates', 'mail_template_id', 'whitelabel_mail_templates_mail_template_id');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_mail_templates', 'whitelabel_mail_templates_mail_template_id');
        \DBUtil::drop_foreign_key('whitelabel_mail_templates', 'whitelabel_mail_templates_whitelabel_id');

        \DBUtil::drop_table('whitelabel_mail_templates');
    }
}
