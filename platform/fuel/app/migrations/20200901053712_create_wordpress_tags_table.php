<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;
use Helper_Migration;

class Create_Wordpress_Tags_Table extends \Database_Migration_Graceful
{
    public function up_gracefully(): void
    {
        DBUtil::create_table(
            'wordpress_tags',
            [
            'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'language_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
            'footer'         => ['type' => 'text'],
        ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key('wordpress_tags', 'language_id')
            ]
        );

        \DBUtil::create_index('wordpress_tags', 'language_id', 'wordpress_tags_language_id_language_idfx_idx');
    }

    public function down_gracefully(): void
    {
        DBUtil::drop_foreign_key('wordpress_tags','wordpress_tags_language_id_foreign');
        DBUtil::drop_index('wordpress_tags', 'wordpress_tags_language_id_language_idfx_idx');
        DBUtil::drop_table('wordpress_tags');
    }
}
