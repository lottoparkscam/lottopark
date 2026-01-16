<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\WhitelabelLottery;

final class Add_Bonus_Balance_Purchase_Limit_Per_User_To_Whitelabel_Lottery extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_lottery';

    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            $this->tableName,
            [
                'bonus_balance_purchase_limit_per_user' => [
                    'type' => 'smallint',
                    'default' => WhitelabelLottery::BONUS_BALANCE_PURCHASE_LIMIT_PER_USER_UNLIMITED,
                    'unsigned' => true,
                    'after' => 'is_bonus_balance_in_use',
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            $this->tableName,
            [
                'bonus_balance_purchase_limit_per_user',
            ]
        );
    }
}
