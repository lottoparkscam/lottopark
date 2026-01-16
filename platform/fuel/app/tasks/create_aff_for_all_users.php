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
 * The task is responsible for creating affiliate accounts for Lottopark users who do not have
 * an affiliate account. In the future, it can be used for another whitelabel by setting
 * the variables: $whitelabelPrefix, $whitelabelId accordingly
 *
 * 1. if an account does not have an affiliate account -> it creates a new affiliate account and links
 * it to the user account
 *
 * 2. if there is more than one account with the same email -> it creates two separate affiliate accounts
 * or links existing affiliate accounts through email
 *
 * 3. if an affiliate account exists but is not linked to a user account -> it links the account by email.
 *  If there are two accounts with the same email, it links them in order
 */
final class Create_Aff_For_All_Users
{
    private WhitelabelUserRepository $whitelabelUserRepository;
    private WhitelabelAffRepository $whitelabelAffRepository;
    private FileLoggerService $logger;

    private const WHITELABEL_ID = 1;
    private const WHITELABEL_PREFIX = 'LP';
    private const LIMIT = 200;

    public function __construct()
    {
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
        $this->logger = Container::get(FileLoggerService::class);
    }

    public function run()
    {
        ini_set('max_execution_time', '300');
        set_time_limit(300);

        $usersWithoutAffiliateAccount = $this->whitelabelUserRepository
            ->getUsersWithoutAffiliateAccount(self::WHITELABEL_ID, self::LIMIT);

        foreach ($usersWithoutAffiliateAccount as $user) {
            DB::start_transaction();

            try {
                $isNotDuplicatedEmail = $user['duplicated_email'] === '0';
                if ($isNotDuplicatedEmail) {
                    [$token, $subAffiliateToken, $affLogin] = $this->generateNewAffiliateData($user);

                    $affiliateAccountId = $this->whitelabelAffRepository->createAffiliateAccount($user, $affLogin, $token, $subAffiliateToken);
                    if ($affiliateAccountId) {
                        $this->whitelabelUserRepository->assignAffiliateIdToUser($user['id'], $affiliateAccountId);
                        $this->whitelabelAffRepository->updateIsAffUser($affiliateAccountId, true);
                    } else {
                        throw new Exception('Error while creating an affiliate account.');
                    }
                } else {
                    $affiliateIds = $this->whitelabelAffRepository->getAffiliateIdsByEmail($user['email'], $user['whitelabel_id']);
                    $affiliateId = $this->getNextAvailableAffiliateId($user['whitelabel_id'], $affiliateIds);

                    if (!$affiliateId) {
                        [$token, $subAffiliateToken, $affLogin] = $this->generateNewAffiliateData($user);
                        $affiliateId = $this->whitelabelAffRepository->createAffiliateAccount($user, $affLogin, $token, $subAffiliateToken);
                    }

                    $this->whitelabelUserRepository->assignAffiliateIdToUser($user['id'], $affiliateId);
                    $this->whitelabelAffRepository->updateIsAffUser($affiliateId, true);
                }

                DB::commit_transaction();
            } catch (Exception $exception) {
                DB::rollback_transaction();

                $userToken = self::WHITELABEL_ID . 'U' . $user['token'];
                $this->logger->error(
                    "[Affiliate account creation task] Could not create an affiliate account 
                    for userToken: {$userToken}. Error: {$exception->getMessage()}"
                );
            }
        }
    }

    private function generateNewAffiliateData(array $user): array
    {
        $token = Lotto_Security::generate_aff_token($user['whitelabel_id']);
        $subAffiliateToken = Lotto_Security::generate_aff_token($user['whitelabel_id']);
        $affLogin = self::WHITELABEL_PREFIX . 'U' . $user['token'];

        return [
            $token,
            $subAffiliateToken,
            $affLogin,
        ];
    }

    /**
     * Retrieves the next available affiliate id
     *
     * @param int $whitelabelId
     * @param array $affiliateIds a list of affiliate ids associated with the user's email
     * @return int|null returns an affiliate ID or null if no ids are available
     */
    private function getNextAvailableAffiliateId(int $whitelabelId, array $affiliateIds): ?int
    {
        $assignedAffiliateIds = $this->whitelabelUserRepository->getAssignedAffiliateIds($affiliateIds, $whitelabelId);

        // find the first affiliate id that has not been assigned yet
        foreach ($affiliateIds as $affiliateId) {
            if (!in_array($affiliateId, $assignedAffiliateIds)) {
                // first unassigned affiliate id
                return $affiliateId;
            }
        }

        // return null if all affiliate IDs have already been assigned
        return null;
    }
}
