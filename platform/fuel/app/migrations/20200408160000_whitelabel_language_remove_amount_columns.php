<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 *
 */
final class Whitelabel_Language_Remove_Amount_Columns extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    public function up_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_language', [
            'min_purchase_amount',
            'min_deposit_amount',
            'min_withdrawal',
            'max_order_amount',
            'max_order_count'
        ]);
    }

    /**
     *
     * @return void
     */
    public function down_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_language', [
            'min_purchase_amount' => [
                'type' => 'decimal',
                'constraint' => [7, 2],
                'unsigned' => true,
                'default' => 0.00
            ],
            'min_deposit_amount' => [
                'type' => 'decimal',
                'constraint' => [7, 2],
                'unsigned' => true,
                'default' => 0.00
            ],
            'min_withdrawal' => [
                'type' => 'decimal',
                'constraint' => [7, 2],
                'unsigned' => true,
                'default' => 10.00
            ],
            'max_order_amount' => [
                'type' => 'decimal',
                'constraint' => [7, 2],
                'unsigned' => true,
                'default' => 1000.00
            ],
            'max_order_count' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'default' => 20
            ],
        ]);
    }
}
