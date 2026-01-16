<?php

namespace Fuel\Migrations;

use Container;
use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;
use Models\WhitelabelTransaction;
use Services\Logs\FileLoggerService;
use Throwable;

final class Add_Missing_Unique_Key_To_Whitelabel_Transaction extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        Helper_Migration::generate_unique_key(WhitelabelTransaction::get_table_name(), ['token', 'whitelabel_id']);
        // Old index that was changed to unique one
        DBUtil::drop_index(WhitelabelTransaction::get_table_name(), 'whitelabel_transaction_w_id_token_idx');
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::drop_unique_key(WhitelabelTransaction::get_table_name(), ['token', 'whitelabel_id']);
        try {
            // Only with this code and in try catch it is possible to rollback migrations many times
            // Without that setting, it is possible to run only once up and down
            DBUtil::create_index(WhitelabelTransaction::get_table_name(), ['token', 'whitelabel_id'], 'whitelabel_transaction_w_id_token_idx');
        } catch (Throwable $exception) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error('Could not insert old index whitelabel_transaction_w_id_token_idx in the migration rollback. Error details: ' . $exception->getMessage());
        }
    }
}
