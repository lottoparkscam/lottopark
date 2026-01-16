<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Country_To_Wordpress_Tags_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('wordpress_tags', [
            'country' => ['type' => 'text'],
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('wordpress_tags', [
            'country',
        ]);
    }
}