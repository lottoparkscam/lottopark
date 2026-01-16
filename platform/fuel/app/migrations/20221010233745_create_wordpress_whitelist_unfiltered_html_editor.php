<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Create_Wordpress_Whitelist_Unfiltered_Html_Editor extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            'wordpress_whitelist_unfiltered_html_editor',
            [
                'id' => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
                'email' => ['type' => 'varchar', 'constraint' => 100],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
        );

        DBUtil::create_index('wordpress_whitelist_unfiltered_html_editor','email','email','unique');
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('wordpress_whitelist_unfiltered_html_editor');
    }
}
