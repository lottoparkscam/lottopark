<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 *
 */
final class Aff_Fb_Pixel extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_aff', [
            'fb_pixel' => [
                'type' => 'varchar',
                'constraint' => 25,
                'null' => true,
                'after' => 'analytics'
            ],
            'fb_pixel_match' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => true,
                'after' => 'fb_pixel',
                'default' => 0
            ]
        ]);
    }

    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_aff',
            [
                'fb_pixel_match',
                'fb_pixel'
            ]
        );
    }
}
