<?php

namespace Fuel\Tasks;

use Container;
use Exception;
use Fuel\Core\DB;
use Lotto_Security;
use Repositories\Aff\WhitelabelAffRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Services\Logs\FileLoggerService;

/**
 * The task is responsible for assigning Is Aff User flag in whitelabel_aff table to users
 * for further use in filters.
 * 
 * is_aff_user = 1 - aff user is connected with whitelabel user
 * is_aff_user = 0 - aff user was created in manager
 * is_aff_user = NULL - default value (1 or 0 not assigned yet)
 */
final class Add_Is_Aff_User_Flag_To_Aff_Users
{
    private WhitelabelAffRepository $whitelabelAffRepository;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private FileLoggerService $logger;

    private const LIMIT = 300;

    public function __construct()
    {
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->logger = Container::get(FileLoggerService::class);
    }

    public function run()
    {
        ini_set('max_execution_time', '300');
        set_time_limit(300);

        $affUsersWithoutIsAffUserFlag = $this->whitelabelAffRepository->getAffUsersWithoutIsAffUserFlag(self::LIMIT);

        foreach ($affUsersWithoutIsAffUserFlag as $user) {
            DB::start_transaction();

            try {
                $affId = $user['id'];
                $isAffIdInUsersTable = $this->whitelabelUserRepository->checkIfAffiliateIdExists($affId);
                $this->whitelabelAffRepository->updateIsAffUser($affId, $isAffIdInUsersTable);

                DB::commit_transaction();
            } catch (Exception $exception) {
                DB::rollback_transaction();

                $this->logger->error("[Add is_aff_user flag to aff users task] Could not set flag for aff userID: {$affId}. Error: {$exception->getMessage()}");
            }
        }
    }
}
