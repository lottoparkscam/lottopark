<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 * Type data estimated extended migration.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-10-25
 * Time: 09:10:30
 */
final class Lottery_Type_Data_Estimated_Extended extends \Database_Migration_Graceful
{

    /**
     * Run migration.
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields('lottery_type_data', [
            'estimated' => ['constraint' => [13, 2], 'type' => 'decimal', 'unsigned' => true]
        ]);
    }

    /**
     * Revert migration.
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        // TODO: {Vordis 2019-10-25 09:45:16} we cannot revert this without potentially losing data, so we will just skip it. better approach would be to catch and ignore Fuel\Core\Database_Exception: 22003 - SQLSTATE[22003]
        // DBUtil::modify_fields('lottery_type_data', [
        //     'estimated' => ['constraint' => [10, 2], 'type' => 'decimal', 'unsigned' => true]
        // ]);
    }
}
