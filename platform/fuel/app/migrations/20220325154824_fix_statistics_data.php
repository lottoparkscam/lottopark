<?php

namespace Fuel\Migrations;

use Container;
use Database_Migration_Graceful;
use Fuel\Core\DB;
use Services\Logs\FileLoggerService;
use Throwable;

final class Fix_Statistics_Data extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        // Clear whitelabel_refer_statistics duplicates
        DB::start_transaction();
        try {
            $duplicatedRowsQuery = "SELECT COUNT(`whitelabel_id`) AS amount, 
                MIN(`whitelabel_id`) AS whitelabel_id, 
                MIN(`whitelabel_user_id`) AS whitelabel_user_id, 
                MIN(`token`) AS token
                FROM `whitelabel_refer_statistics`
                GROUP BY `whitelabel_id`, `whitelabel_user_id`
                HAVING amount > 1";

            /** @var mixed $query */
            $query = DB::query($duplicatedRowsQuery)->execute();
            $duplicatedRows = $query->as_array();
            foreach ($duplicatedRows as $duplicatedRow) {
                $token = $duplicatedRow['token'];
                $whitelabelUserId = $duplicatedRow['whitelabel_user_id'];
                $whitelabelId = $duplicatedRow['whitelabel_id'];
                $rowsWithTheSameTokenQuery = "SELECT * FROM `whitelabel_refer_statistics` 
                    WHERE `token` = :token 
                    AND `whitelabel_user_id` = :whitelabel_user_id 
                    AND `whitelabel_id` = :whitelabel_id";

                /** @var mixed $query */
                $query = DB::query($rowsWithTheSameTokenQuery)
                    ->param(':token', $token)
                    ->param(':whitelabel_user_id', $whitelabelUserId)
                    ->param(':whitelabel_id', $whitelabelId)
                    ->execute();

                $rowsWithTheSameToken = $query->as_array();

                $idToAddData = (int)$rowsWithTheSameToken[0]['id'];
                foreach ($rowsWithTheSameToken as $row)
                {
                    // This row will be updated
                    $isFirstRow = (int)$row['id'] === $idToAddData;
                    if ($isFirstRow) {
                        continue;
                    }

                    $updateQuery = "UPDATE `whitelabel_refer_statistics`
                        SET `clicks` = `clicks` + :clicks,
                        `unique_clicks` = `unique_clicks` + :unique_clicks,
                        `registrations` = `registrations` + :registrations,
                        `free_tickets` = `free_tickets` + :free_tickets
                        WHERE `id` = :id";
                    DB::query($updateQuery)
                        ->param(':clicks', (int)$row['clicks'])
                        ->param(':unique_clicks', (int)$row['unique_clicks'])
                        ->param(':registrations', (int)$row['registrations'])
                        ->param(':free_tickets', (int)$row['free_tickets'])
                        ->param(':id', $idToAddData)
                        ->execute();

                    $deleteQuery = "DELETE FROM `whitelabel_refer_statistics` WHERE `id` = :id";
                    DB::query($deleteQuery)
                        ->param(':id', (int)$row['id'])
                        ->execute();
                }
            }
        } catch (Throwable $throwable) {
            DB::rollback_transaction();
            $logger = Container::get(FileLoggerService::class);
            $logger->error($throwable->getMessage());
        }
    }

    protected function down_gracefully(): void
    {
    }
}