<?php

namespace Repositories\Orm;

use Carbon\Carbon;
use Exception;
use Fuel\Core\Database_Result;
use Fuel\Core\DB;
use Helpers_Crm_General;
use Classes\Orm\Criteria\{
    Model_Orm_Criteria_Select,
    Model_Orm_Criteria_Where,
    Model_Orm_Criteria_Order,
    With\Model_Orm_Criteria_With_Relation,
    Rows\Model_Orm_Criteria_Rows_Limit
};
use Container;
use Fuel\Core\Session;
use Helpers\UserHelper;
use Helpers\WhitelabelHelper;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Modules\Mediacle\Models\MediaclePlayerRegistrationData;
use Modules\Mediacle\Models\PlayerDataWhitelabelUserModelAdapter;
use Modules\Mediacle\Repositories\PlayerRegistrationDataByIdContract;
use Modules\Mediacle\Repositories\PlayerRegistrationsByDateContract;
use Lotto_Security;
use Services\Logs\FileLoggerService;
use Throwable;

/** @method WhitelabelUser|null findOneById(int $userId) */
class WhitelabelUserRepository extends AbstractRepository implements PlayerRegistrationDataByIdContract, PlayerRegistrationsByDateContract
{
    protected Whitelabel $whitelabel;
    private FileLoggerService $fileLoggerService;

    public function __construct(WhitelabelUser $model, FileLoggerService $fileLoggerService)
    {
        parent::__construct($model);
        $this->fileLoggerService = $fileLoggerService;
    }

    /**
     * @param string $login
     * @param int $whitelabelId
     * @return WhitelabelUser|null
     * @throws Exception
     */
    public function findEnabledUserByLogin(string $login, int $whitelabelId): ?WhitelabelUser
    {
        /** @var WhitelabelUser $whitelabelUsers */
        $whitelabelUsers =  $this->pushCriterias([
            new Model_Orm_Criteria_Where('login', $login),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('is_deleted', false),
            new Model_Orm_Criteria_Where('is_active', true)
        ])->getResults();

        if (count($whitelabelUsers) > 1) {
            throw new Exception("Found many users with login: $login when try to find one.");
        }

        return $whitelabelUsers ? reset($whitelabelUsers) : null;
    }

    /**
     * @param string $email
     * @param int $whitelabelId
     * @return WhitelabelUser|null
     * @throws Exception
     */
    public function findEnabledUserByEmail(string $email, int $whitelabelId): ?WhitelabelUser
    {
        /** @var WhitelabelUser $whitelabelUsers */
        $whitelabelUsers = $this->pushCriterias([
            new Model_Orm_Criteria_Where('email', $email),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('is_deleted', false),
            new Model_Orm_Criteria_Where('is_active', true)
        ])->getResults();

        if (count($whitelabelUsers) > 1) {
            throw new Exception("Found many users with email: $email when try to find one.");
        }

        return $whitelabelUsers ? reset($whitelabelUsers) : null;
    }

    /**
     * @param string|null $login
     * @param string|null $email
     * @param Whitelabel $whitelabel
     * @return WhitelabelUser|null
     * @throws Exception
     */
    public function findSpecificUser(?string $login, ?string $email, Whitelabel $whitelabel): ?WhitelabelUser
    {
        $identifyByLogin = $whitelabel->useLoginsForUsers;
        $whitelabelUser = $this->findEnabledUserByEmail($email, $whitelabel->id);

        if ($identifyByLogin) {
            $whitelabelUser = $this->findEnabledUserByLogin($login, $whitelabel->id);
        }

        return $whitelabelUser;
    }

    public function getPlayerById(int $playerId): MediaclePlayerRegistrationData
    {
        return new PlayerDataWhitelabelUserModelAdapter(
            $this->getById($playerId, ['whitelabel.whitelabel_plugins'])
        );
    }

    /**
     * @inheritDoc
     */
    public function findPlayerRegistrationsByDate(int $whitelabelId, Carbon $date): array
    {
        $dateFrom = $date->format('Y-m-d') . ' 00:00:00';
        $dateTo = $date->format('Y-m-d') . ' 23:59:59';

        $this->pushCriteria(new Model_Orm_Criteria_Where('date_register', $dateFrom, '>='));
        $this->pushCriteria(new Model_Orm_Criteria_Where('date_register', $dateTo, '<='));

        $this->pushCriteria(new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId));

        $this->pushCriteria(new Model_Orm_Criteria_With_Relation('whitelabel'));
        $this->pushCriteria(new Model_Orm_Criteria_With_Relation('whitelabel_user_promo_code.whitelabel_promo_code.whitelabel_campaign'));
        $this->pushCriteria(new Model_Orm_Criteria_With_Relation('whitelabel_user_aff.whitelabel_aff'));

        $this->orderBy('date_register', 'ASC');
        $this->orderBy('id', 'ASC');

        $results = $this->getResults();
        return array_map(fn (WhitelabelUser $user) => new PlayerDataWhitelabelUserModelAdapter($user), $results);
    }

    public function findByTokenAndWhitelabelId(string $token, int $whitelabelId): ?WhitelabelUser
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('token', $token),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId)
        ]);

        return $this->findOne();
    }

    /**
     * This table looks like there were 10 inner join due to many columns,
     * so specifying select would have good impact on performance
     */
    public function findByTokenWithSpecifiedSelect(
        string $token,
        int $whitelabelId,
        array $selectFields = []
    ): ?WhitelabelUser {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select($selectFields),
            new Model_Orm_Criteria_Where('token', $token),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId)
        ]);

        return $this->findOne();
    }

    /**
     * This method auto detects current whitelabel
     * @return ?WhitelabelUser null if user isn't logged in
     */
    public function getUserFromSession(): ?WhitelabelUser
    {
        if (UserHelper::isUserSessionIncorrect()) {
            return null;
        }

        try {
            /** @var Whitelabel $whitelabel */
            $whitelabel = Container::get('whitelabel');
        } catch (Throwable $e) {
            $this->fileLoggerService->error(
                'Cannot find whitelabel from container' . $e->getMessage()
            );
            return null;
        }

        $uniqueLoginField = WhitelabelHelper::getLoginField();
        $email = Session::get('user.email');
        $hash = Session::get('user.hash');

        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabel->id),
            new Model_Orm_Criteria_Where('is_deleted', 0),
            new Model_Orm_Criteria_Where($uniqueLoginField, null, 'IS NOT'),
            new Model_Orm_Criteria_Where('email', $email),
            new Model_Orm_Criteria_Where('hash', $hash)
        ]);

        return $this->findOne() ?? null;
    }

    /**
     * Get whitelabel user during login
     * @param string $login provided login or email
     */
    public function getUser(
        int $whitelabelId,
        string $login,
        string $password
    ): ?WhitelabelUser {
        $uniqueLoginField = WhitelabelHelper::getLoginField();

        $this->pushCriterias([
            new Model_Orm_Criteria_Select(
                [
                    'id', 'token', 'hash', 'salt', 'is_active', 'is_confirmed', 'email', 'login',
                ]
            ),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('is_deleted', false),
            new Model_Orm_Criteria_Where($uniqueLoginField, null, 'IS NOT'),
            new Model_Orm_Criteria_Where($uniqueLoginField, $login)
        ]);

        /** @var ?WhitelabelUser $whitelabelUser */
        $whitelabelUser = $this->findOne();
        if (is_null($whitelabelUser)) {
            return null;
        }

        $hasWrongPassword = $whitelabelUser->hash !== Lotto_Security::generate_hash($password, $whitelabelUser->salt);
        if ($hasWrongPassword) {
            return null;
        }

        return $whitelabelUser;
    }

    /** Get user for /autologin endpoint */
    public function getUserByLoginHash(int $whitelabelId, string $loginHash): ?WhitelabelUser
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('login_hash', null, 'IS NOT'),
            new Model_Orm_Criteria_Where('login_hash', $loginHash),
            new Model_Orm_Criteria_Where('is_deleted', false),
            new Model_Orm_Criteria_Where('is_active', true),
        ]);

        return $this->findOne();
    }

    /**
     * Return only Whitelabel Users after the specified ID.
     */
    public function getUsersAfterId(int $whitelabelId, ?int $afterId = null, ?int $limit = null, array $criterias = []): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Order('id', 'ASC'),
        ]);

        if ($afterId !== null) {
            $this->pushCriteria(new Model_Orm_Criteria_Where('id', $afterId, '>'));
        }

        if ($limit !== null) {
            $this->pushCriteria(new Model_Orm_Criteria_Rows_Limit($limit));
        }

        if (!empty($criterias)) {
            $this->pushCriterias($criterias);
        }

        return $this->getResults();
    }

    public function getLastUserId(int $whitelabelId, array $criterias = []): ?int
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Order('id', 'DESC'),
            new Model_Orm_Criteria_Rows_Limit(1)
        ]);

        if (!empty($criterias)) {
            $this->pushCriterias($criterias);
        }

        $last = $this->findOne();

        return $last?->id;
    }

    public function getFTPDataForCRMChartForLastSevenDays(?int $whitelabelId): array
    {
        $query = $this->db->select(
            $this->db->expr('COUNT(*) AS count'),
            $this->db->expr('DATE(first_purchase) AS date')
        )
            ->from($this->model::get_table_name())
            ->where('first_purchase', '>=', $this->db->expr('DATE(NOW()) - INTERVAL 7 DAY'));

        $isNotCrmSuperadmin = $whitelabelId > 0;
        if ($isNotCrmSuperadmin) {
            $query->and_where('whitelabel_id', $whitelabelId);
        }

        $query->group_by($this->db->expr('DATE(first_purchase)'));

        /** @var Database_Result $result */
        $result = $query->execute();

        return $result->as_array();
    }

    public function getFTPCountForCRM(?int $whitelabelId, string $startDate, string $endDate): int
    {
        $start = Helpers_Crm_General::prepare_start_date($startDate);
        $end = Helpers_Crm_General::prepare_end_date($endDate);

        $query = $this->db->select(
            $this->db->expr('COUNT(*) as count')
        )
            ->from($this->model::get_table_name())
            ->where('first_purchase', '!=', null)
            ->and_where('first_purchase', '>=', $start)
            ->and_where('first_purchase', '<=', $end);

        $isNotCrmSuperadmin = $whitelabelId > 0;
        if ($isNotCrmSuperadmin) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        /** @var Database_Result $result */
        $result = $query->execute();

        return $result->as_array()[0]['count'] ?? 0;
    }

    public function getLastPurchasedLotteryNameByUsersIds(array $whitelabelUserIds): array
    {
        $query = <<<QUERY
SELECT 
wu.id AS id,
(SELECT l.name
FROM whitelabel_user_ticket wut
LEFT JOIN lottery l
ON l.id = wut.lottery_id
WHERE wut.whitelabel_transaction_id IS NOT NULL
AND wut.paid = 1
AND wut.whitelabel_user_id = wu.id
ORDER BY wut.date DESC
LIMIT 1
) AS lottery_name
FROM whitelabel_user AS wu
WHERE wu.id IN :whitelabelUserIds
QUERY;

        /** @var Database_Result $results */
        $results = $this->db->query($query)
            ->param(':whitelabelUserIds', $whitelabelUserIds)
            ->execute();

        $preparedResult = [];
        foreach ($results->as_array() as $user) {
            $preparedResult[$user['id']] = $user['lottery_name'];
        }

        return $preparedResult;
    }

    public function findUserByEmailAndWhitelabelId(string $email, int $whitelabelId): ?WhitelabelUser
    {
        /** @var WhitelabelUser $whitelabelUser */
        $whitelabelUser = $this->pushCriterias([
            new Model_Orm_Criteria_Where('email', $email),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('is_deleted', false),
        ])->findOne();

        return $whitelabelUser;
    }

    public function getUserAsArrayForCrmUserDetails(int $userId): array
    {
        $query = $this->db->select(
            'whitelabel_user.address_1',
            'whitelabel_user.address_2',
            'whitelabel_user.name',
            'whitelabel_user.surname',
            'whitelabel_user.country',
            'whitelabel_user.city',
            'whitelabel_user.state',
            'whitelabel_user.birthdate',
            'whitelabel_user.phone',
            'whitelabel_user.prize_payout_whitelabel_user_group_id',
            'whitelabel_user.date_register',
            'whitelabel_user.currency_id',
            'whitelabel_user.language_id',
            'whitelabel_user.gender',
            'whitelabel_user.national_id',
            'whitelabel_user.first_purchase',
            'whitelabel_user.balance',
            'whitelabel_user.bonus_balance',
            'whitelabel_user.casino_balance',
            'whitelabel_user.token',
            'whitelabel_user.email',
            'whitelabel_user.login',
            'whitelabel_user.phone',
            'whitelabel_user.timezone',
            'whitelabel_user.city',
            'whitelabel_user.state',
            'whitelabel_user.zip',
            'whitelabel_user.connected_aff_id',
            'whitelabel_user.register_ip',
            'whitelabel_user.register_country',
            'whitelabel_user.last_ip',
            'whitelabel_user.first_deposit_amount_manager',
            'whitelabel_user.last_deposit_amount_manager',
            'whitelabel_user.second_deposit',
            'whitelabel_user.last_active',
            'whitelabel_user.last_deposit_date',
            'whitelabel_user.last_country',
            'whitelabel_user.first_deposit',
            'whitelabel_user.first_purchase',
            'whitelabel_user.second_purchase',
            'whitelabel_user.last_purchase_date',
            'whitelabel_user.total_deposit_manager',
            'whitelabel_user.total_withdrawal_manager',
            'whitelabel_user.total_purchases_manager',
            'whitelabel_user.total_net_income_manager',
            'whitelabel_user.last_purchase_amount_manager',
            'whitelabel_user.sale_status',
            'whitelabel_user.pnl_manager',
            'whitelabel_user.system_type',
            'whitelabel_user.browser_type',
            'whitelabel_user.net_winnings_manager',
            'whitelabel_user.last_update',
            'whitelabel_user.prize_payout_whitelabel_user_group_id',
            'whitelabel_user.date_register',
            'whitelabel_user.casino_balance',
            'whitelabel_user.is_confirmed',
            ['language.code', 'language_code'],
            ['whitelabel.name', 'whitelabel_name'],
            ['whitelabel.prefix', 'whitelabel_prefix'],
            ['c1.code', 'whitelabel_currency_code'],
            ['c1.rate', 'whitelabel_currency_rate'],
            ['c2.id', 'user_currency_id'],
            ['c2.code', 'user_currency_code'],
            ['c2.rate', 'user_currency_rate'],
            ['whitelabel_user_group.name', 'group_name'],
            [$this->db->expr('DATEDIFF(NOW(), whitelabel_user.date_register)'), 'player_lifetime']
        )
            ->from('whitelabel_user')
            ->join('whitelabel')->on('whitelabel_user.whitelabel_id', '=', 'whitelabel.id')
            ->join('language', 'LEFT')->on('whitelabel_user.language_id', '=', 'language.id')
            ->join(['currency', 'c1'])->on('whitelabel.manager_site_currency_id', '=', 'c1.id')
            ->join(['currency', 'c2'])->on('whitelabel_user.currency_id', '=', 'c2.id')
            ->join('whitelabel_user_group', 'LEFT')->on('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', 'whitelabel_user_group.id')
            ->where('whitelabel_user.id', '=', $userId);

        /** @var Database_Result $result */
        $result = $query->execute();
        return $result->as_array()[0];
    }

    public function getRegisterCountByAffiliateId(?int $whitelabelId, string $startDate, string $endDate): int
    {
        $start = Helpers_Crm_General::prepare_start_date($startDate);
        $end = Helpers_Crm_General::prepare_end_date($endDate);

        $query = $this->db->select(
            $this->db->expr('COUNT(*) as count')
        )
            ->from($this->model::get_table_name())
            ->where('first_purchase', '!=', null)
            ->and_where('first_purchase', '>=', $start)
            ->and_where('first_purchase', '<=', $end);

        $isNotCrmSuperadmin = $whitelabelId > 0;
        if ($isNotCrmSuperadmin) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        /** @var Database_Result $result */
        $result = $query->execute();

        return $result->as_array()[0]['count'] ?? 0;
    }

    /**
     * The method is intended to retrieve only those users who do not have an
     * affiliate account and for whom no affiliate account has been created using their email
     *
     * @param int $whitelabelId
     * @param int $limit
     * @return array
     */
    public function getUsersWithoutAffiliateAccount(int $whitelabelId, int $limit): array
    {
        $subQuery = 'SELECT 1 FROM whitelabel_aff wa WHERE wa.email = whitelabel_user.email';
        $query = $this->db->selectArray([
            'id',
            'token',
            'whitelabel_id',
            'language_id',
            'currency_id',
            'login',
            'email',
            'hash',
            'salt',
            'address_1',
            'address_2',
            'city',
            'country',
            'state',
            'zip',
            'phone_country',
            'phone',
            'timezone',
            DB::expr("IF(EXISTS ($subQuery), 1, 0) AS duplicated_email")
        ])
            ->from(WhitelabelUser::table())
            ->where('whitelabel_id', '=', $whitelabelId)
            ->where('connected_aff_id', 'IS', null)
            ->order_by('id', 'asc')
            ->limit($limit);

        /** @var Database_Result $result */
        $result = $query->execute();
        return $result->as_array();
    }

    public function assignAffiliateIdToUser(int $userId, int $affiliateId): void
    {
        $this->db->update(WhitelabelUser::get_table_name())
            ->set([
                'connected_aff_id' => $affiliateId,
            ])
            ->where('id', $userId)
            ->execute();
    }

    public function getAssignedAffiliateIds(array $affiliateIds, int $whitelabelId): array
    {
        if (empty($affiliateIds)) {
            return [];
        }

        /** @var mixed $query */
        $query = $this->db->select('connected_aff_id')
            ->from(WhitelabelUser::table())
            ->where('whitelabel_id', $whitelabelId)
            ->where('connected_aff_id', 'IN', $affiliateIds)
            ->order_by('id', 'asc');

        /** @var Database_Result $result */
        $result = $query->execute()->as_array();

        return array_map(function ($row) {
            return $row['connected_aff_id'];
        }, $result);
    }

    public function checkIfAffiliateIdExists(int $affId): bool
    {
        /** @var mixed $query */
        $query = $this->db->select('connected_aff_id')
            ->from(WhitelabelUser::table())
            ->where('connected_aff_id', '=', $affId);

        /** @var Database_Result $result */
        $result = $query->execute();

        return (!empty($result->as_array())) ? true : false;
    }

    public function updateUserBalance(int $userId, float $amount): void
    {
        $this->db->update(WhitelabelUser::table())
            ->set([
                'balance' => DB::expr("balance + $amount"),
            ])
            ->where('id', $userId)
            ->execute();
    }

    public function updateUserBonusBalance(int $userId, float $amount): void
    {
        $this->db->update(WhitelabelUser::table())
            ->set([
                'bonus_balance' => DB::expr("bonus_balance + $amount"),
            ])
            ->where('id', $userId)
            ->execute();
    }
}
