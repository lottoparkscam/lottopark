<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Minimum_Expected_Income_To_Whitelabel_Lottery_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_lottery',
            [
                'minimum_expected_income' => [
                    'type' => 'decimal',
                    'constraint' => [5,2],
                    'null' => true,
                    'default' => null,
                    'after' => 'income',
                    'unsigned' => true
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_lottery',
            [
                'minimum_expected_income',
            ]
        );
    }
}