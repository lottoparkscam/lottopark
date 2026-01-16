<?php

namespace Repositories\Aff;

use Carbon\Carbon;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Fuel\Core\Database_Result;
use Repositories\Orm\AbstractRepository;
use Models\WhitelabelAff;

/**
 * @method findOneByToken(string $TRAFFIC_BAR_TOKEN)
 * @method WhitelabelAff findOneByToken(string $token)
 */
class WhitelabelAffRepository extends AbstractRepository
{
    public function __construct(WhitelabelAff $whitelabelAff)
    {
        parent::__construct($whitelabelAff);
    }

    public function findAffiliateAccountByLoginOrEmail(string $loginOrEmail, int $whitelabelId): array
    {
        /** @var mixed $query */
        $query = $this->db->selectArray([
            'salt',
            'hash',
            'is_active',
            'is_confirmed'
        ])
            ->from(WhitelabelAff::table())
            ->where('whitelabel_id', $whitelabelId)
            ->and_where('is_deleted', false)
            ->where_open()
            ->and_where('login', $loginOrEmail)
            ->or_where('email', $loginOrEmail)
            ->where_close();

        return $query->execute()->as_array()[0] ?? [];
    }

    public function findAffiliateById(int $id): WhitelabelAff
    {
        $this->pushCriteria(new Model_Orm_Criteria_Where('id', $id));

        return $this->findOne();
    }

    public function findAffiliateByEmail(string $email, int $whitelabelId): ?WhitelabelAff
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('email', $email),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
        ]);

        return $this->findOne();
    }

    public function findAffiliatesByWhitelabel(int $whitelabelId): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select([
                'id',
                'name',
                'surname',
                'login',
                'is_deleted',
                'is_active',
                'is_accepted',
            ]),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('is_deleted', false),
            new Model_Orm_Criteria_Where('is_active', true),
            new Model_Orm_Criteria_Where('is_accepted', true),
            new Model_Orm_Criteria_Order('name', 'asc'),
            new Model_Orm_Criteria_Order('surname', 'asc'),
            new Model_Orm_Criteria_Order('login', 'asc'),
        ]);

        return $this->getResults();
    }

    public function findAffiliateByPasswordResetHash(string $hash, int $whitelabelId): ?WhitelabelAff
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('password_reset_hash', $hash),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
        ]);

        return $this->findOne();
    }

    public function findAffiliateByToken(int $whitelabelId, string $token): ?WhitelabelAff
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('is_deleted', false),
            new Model_Orm_Criteria_Where('is_active', true),
            new Model_Orm_Criteria_Where('is_accepted', true),
            new Model_Orm_Criteria_Where('token', $token),
        ]);

        return $this->findOne();
    }

    public function findAffiliateByTokenOrSubToken(array $whitelabel, string $tokenOrSubtoken): array
    {
        /** @var mixed $query */
        $query = $this->db->selectArray([
            'id',
            'sub_affiliate_token',
            'token',
        ])
            ->from(WhitelabelAff::table())
            ->where('whitelabel_id', $whitelabel['id'])
            ->and_where('is_deleted', false)
            ->and_where('is_active', true)
            ->and_where('is_accepted', true)
            ->where_open()
            ->and_where('token', $tokenOrSubtoken)
            ->or_where('sub_affiliate_token', $tokenOrSubtoken)
            ->where_close();

        return $query->execute()->as_array()[0] ?? [];
    }

    public function findAffiliateCountByTokenOrSubToken(int $whitelabelId, string $tokenOrSubtoken): int
    {
        $query = $this->db->select($this->db->expr('COUNT(id) as count'))
            ->from(WhitelabelAff::table())
            ->where('whitelabel_id', $whitelabelId)
            ->and_where('is_deleted', false)
            ->and_where('is_active', true)
            ->and_where('is_accepted', true)
            ->where_open()
            ->and_where('token', $tokenOrSubtoken)
            ->or_where('sub_affiliate_token', $tokenOrSubtoken)
            ->where_close();

        return (int)$query->execute()[0]['count'] ?? 0;
    }

    public function findSubAffiliateIdsByParentAffiliateId(int $parentId): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['id']),
            new Model_Orm_Criteria_Where('whitelabel_aff_parent_id', $parentId),
            new Model_Orm_Criteria_Where('is_deleted', false),
            new Model_Orm_Criteria_Where('is_active', true),
            new Model_Orm_Criteria_Where('is_accepted', true)
        ]);

        return $this->getResults();
    }

    public function findSubAffiliatesByParentId(int $parentId, object $pagination, array $filters)
    {
        $query = $this->db->selectArray([
            'token', 'name',
            'surname', 'login',
            'email', 'phone',
            'country', $this->db->expr('l.code as lcode'),
            'timezone', 'date_created',
            'last_active', 'last_ip',
            'last_country', 'is_confirmed',
            $this->db->expr('wa.id as id'), $this->db->expr('l.code as lcode'),
        ])
            ->from($this->db->expr('whitelabel_aff as wa'))
            ->join($this->db->expr('language as l'), 'LEFT')
            ->on('l.id', '=', 'language_id')
            ->where('whitelabel_aff_parent_id', $parentId)
            ->and_where('is_deleted', false)
            ->and_where('is_active', true)
            ->and_where('is_accepted', true);

        foreach ($filters as $filter) {
            $query->and_where($filter[0], 'LIKE', $filter[1]);
        }

        $query->order_by('date_created', 'desc')
            ->limit($pagination->per_page)
            ->offset($pagination->offset);

        return $query->execute();
    }

    public function countSubAffiliatesByParentId(int $parentId, array $filters): int
    {
        $query = $this->db->select($this->db->expr('COUNT(id) as count'))
            ->from('whitelabel_aff')
            ->where('whitelabel_aff_parent_id', $parentId)
            ->and_where('is_deleted', false)
            ->and_where('is_active', true)
            ->and_where('is_accepted', true);

        foreach ($filters as $filter) {
            $query->and_where($filter[0], 'LIKE', $filter[1]);
        }

        return (int)$query->execute()[0]['count'] ?? 0;
    }

    public function insert(
        array $whitelabel,
        ?int $affiliateParentId,
        string $newToken,
        string $newSubAffiliateToken,
        int $defaultLangId,
        ?int $whitelabelAffGroupId,
        int $isActive,
        int $isConfirmed,
        int $isAccepted,
        string $login,
        string $email,
        string $hash,
        string $salt
    ): WhitelabelAff
    {
        $affiliate = new WhitelabelAff;
        $affiliate->whitelabelId = $whitelabel['id'];
        $affiliate->whitelabelAffParentId = $affiliateParentId;
        $affiliate->token = $newToken;
        $affiliate->subAffiliateToken = $newSubAffiliateToken;
        $affiliate->currencyId = $whitelabel['manager_site_currency_id'];
        $affiliate->languageId = $defaultLangId;
        $affiliate->whitelabelAffGroupId = $whitelabelAffGroupId;
        $affiliate->isActive = $isActive;
        $affiliate->isConfirmed = $isConfirmed;
        $affiliate->isAccepted = $isAccepted;
        $affiliate->login = $login;
        $affiliate->email = $email;
        $affiliate->hash = $hash;
        $affiliate->salt = $salt;
        $affiliate->isDeleted = 0;
        $affiliate->name = '';
        $affiliate->surname = '';
        $affiliate->address1 = '';
        $affiliate->address2 = '';
        $affiliate->city = '';
        $affiliate->country = '';
        $affiliate->state = '';
        $affiliate->zip = '';
        $affiliate->phoneCountry = '';
        $affiliate->birthdate = null;
        $affiliate->phone = '';
        $affiliate->timezone = '';
        $affiliate->affLeadLifetime = 0; // unlimited
        $affiliate->isShowName = 0;
        $affiliate->hideLeadId = 0;
        $affiliate->hideTransactionId = 0;
        $affiliate->dateCreated = Carbon::now();

        $affiliate->save();

        return $affiliate;
    }

    public function findAffiliatesWithoutSubtoken(): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('sub_affiliate_token', null),
        ]);

        return $this->getResults();
    }

    public function updatePasswordResetHash(int $affId, string $hash): void
    {
        $aff = $this->findAffiliateById($affId);
        $aff->passwordResetHash = $hash;

        $aff->save();
    }

    public function getAffiliateIdsByEmail(string $email, int $whitelabelId): array
    {
        /** @var mixed $query */
        $query = $this->db->select('id')
            ->from(WhitelabelAff::table())
            ->where('whitelabel_id', $whitelabelId)
            ->where('email', $email)
            ->order_by('id', 'asc');

        return $query->execute()->as_array()[0] ?? [];
    }

    public function createAffiliateAccount(array $user, string $login, string $token, string $subAffiliateToken): int
    {
        $aff = new WhitelabelAff();
        $aff->whitelabelId = $user['whitelabel_id'];
        $aff->languageId = $user['language_id'];
        $aff->currencyId = $user['currency_id'];
        $aff->isAccepted = 1;
        $aff->isConfirmed = 1;
        $aff->isActive = 1;
        $aff->login = $login;
        $aff->email = $user['email'];
        $aff->isDeleted = 0;
        $aff->token = $token;
        $aff->subAffiliateToken = $subAffiliateToken;
        $aff->hash = $user['hash'];
        $aff->salt = $user['salt'];
        $aff->address1 = $user['address_1'];
        $aff->address2 = $user['address_2'];
        $aff->city = $user['city'];
        $aff->country = $user['country'];
        $aff->state = $user['state'];
        $aff->zip = $user['zip'];
        $aff->phoneCountry = $user['phone_country'];
        $aff->phone = $user['phone'];
        $aff->timezone = $user['timezone'];
        $aff->affLeadLifetime = 0;
        $aff->dateCreated = Carbon::now()->format('Y-m-d H:i:s');
        $aff->isShowName = 0;
        $aff->hideLeadId = true;
        $aff->hideTransactionId = true;
        $aff->save();

        return $aff->id;
    }

    public function getAffiliatesDetailsByIds(array $affiliateIds, int $whitelabelId): array
    {
        if (empty($affiliateIds)) {
            return [];
        }

        /** @var mixed $query */
        $query = $this->db->selectArray([
            'id',
            'name',
            'surname',
            'token',
            'login',
        ])
            ->from(WhitelabelAff::table())
            ->where('whitelabel_id', $whitelabelId)
            ->where('id', 'IN', $affiliateIds)
            ->order_by('id', 'asc');

        return $query->execute()->as_array() ?? [];
    }

    public function getAffUsersWithoutIsAffUserFlag(int $limit): array
    {
        /** @var mixed $query */
        $query = $this->db->select('id')
            ->from(WhitelabelAff::table())
            ->where('is_aff_user', '=', null)
            ->limit($limit);

        /** @var Database_Result $result */
        $result = $query->execute();

        return $result->as_array();
    }

    public function updateIsAffUser(int $affId, bool $value): void
    {
        $this->db->update(WhitelabelAff::table())
            ->set(['is_aff_user' => $value])
            ->where('id', '=', $affId)
            ->execute();
    }

    public function getAffiliateParentTokenByWhitelabelUserId(int $userId): string
    {
        /** @var mixed $query */
        $query = $this->db->select('connected_aff_id')
            ->from('whitelabel_user')
            ->where('id', '=', $userId);

        /** @var Database_Result $result */
        $result = $query->execute();
        $connectedAffId = $result->get('connected_aff_id');

        if (empty($connectedAffId)) {
            return '';
        }

        /** @var mixed $query */
        $query = $this->db->select('whitelabel_aff_parent_id')
            ->from('whitelabel_aff')
            ->where('id', '=', $connectedAffId);

        /** @var Database_Result $result */
        $result = $query->execute();
        $parentId = $result->get('whitelabel_aff_parent_id');

        if (empty($parentId)) {
            return '';
        }

        /** @var mixed $query */
        $query = $this->db->select('token')
            ->from('whitelabel_aff')
            ->where('id', '=', $parentId);

        /** @var Database_Result $result */
        $result = $query->execute();
        $parentToken = $result->get('token');

        return $parentToken ?? '';
    }
}
