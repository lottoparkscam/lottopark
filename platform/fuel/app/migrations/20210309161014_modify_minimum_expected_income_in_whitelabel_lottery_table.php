<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Modify_Minimum_Expected_Income_In_Whitelabel_Lottery_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            'whitelabel_lottery',
            [
                'minimum_expected_income' => [
                    'type' => 'decimal',
                    'constraint' => [5,2],
                    'null' => true,
                    'default' => 1.00,
                    'unsigned' => true
                ]
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            'whitelabel_lottery',
            [
                'minimum_expected_income' => [
                    'type' => 'decimal',
                    'constraint' => [5,2],
                    'null' => true,
                    'default' => null,
                    'unsigned' => true
                ]
            ]
        );
    }
}