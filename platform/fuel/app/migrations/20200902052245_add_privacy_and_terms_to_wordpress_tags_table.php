<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Privacy_And_Terms_To_Wordpress_Tags_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('wordpress_tags', [
            'privacy_policy' => ['type' => 'text'],
            'terms_and_conditions' => ['type' => 'text']
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('wordpress_tags', [
            'privacy_policy',
            'terms_and_conditions'
        ]);
    }
}