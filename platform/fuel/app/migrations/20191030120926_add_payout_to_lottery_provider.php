<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 * Add payout to lottery provider migration.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-10-30
 * Time: 12:09:37
 */
final class Add_Payout_to_lottery_provider extends \Database_Migration_Graceful
{

    /**
     * Run migration.
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('lottery_provider', [
            'max_payout' => ['constraint' => [13, 2], 'type' => 'decimal', 'unsigned' => true, 'default' => 0]
        ]);
    }

    /**
     * Revert migration.
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('lottery_provider', 'max_payout');
    }
}
