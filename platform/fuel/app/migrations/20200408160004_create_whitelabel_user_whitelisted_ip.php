<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 *
 */
final class Create_whitelabel_user_whitelisted_ip extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    public function up_gracefully(): void
    {
        DBUtil::create_table(
            'whitelabel_user_whitelisted_ip',
            [
            'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
            'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'ip' => ['type' => 'varchar', 'constraint' => 45],
        ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'key' => 'whitelabel_user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ]
                ],
            ]
        );
    }

    /**
     *
     * @return void
     */
    public function down_gracefully(): void
    {
        DBUtil::drop_table('whitelabel_user_whitelisted_ip');
    }
}
