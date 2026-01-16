<?php

namespace Fuel\Migrations;

class Mail_Templates
{
    public function up()
    {
        \DBUtil::create_table(
            'mail_templates',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'slug' => ['type' => 'varchar', 'constraint' => 20],
                'title' => ['type' => 'varchar', 'constraint' => 100],
                'content' => ['type' => 'text', 'null' => true],
                'additional_translates' => ['type' => 'text', 'null' => true],
                'is_partial' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );
    }

    public function down()
    {
        \DBUtil::drop_table('mail_templates');
    }
}
