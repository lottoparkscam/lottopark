<?php

namespace Repositories\Aff;

use Repositories\Orm\AbstractRepository;
use Models\WhitelabelUserAff;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;

/**
 * @method findOneByWhitelabelUserId(int $id)
 */
class WhitelabelUserAffRepository extends AbstractRepository
{
    public function __construct(WhitelabelUserAff $whitelabelUserAff)
    {
        parent::__construct($whitelabelUserAff);
    }

    public function findUserAffiliate(int $whitelabelId, int $whitelabelUserId): ?WhitelabelUserAff
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('whitelabel_user_id', $whitelabelUserId),
            new Model_Orm_Criteria_Where('is_deleted', false),
            new Model_Orm_Criteria_Where('is_expired', false),
        ]);

        return $this->findOne();
    }

    public function findUserAffiliateByWhitelabelIdAndRefToken(int $whitelabelId, string $token, int $whitelabelUserId): ?WhitelabelUserAff
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('whitelabel_aff'),
            new Model_Orm_Criteria_Where('whitelabel_aff.is_deleted', false),
            new Model_Orm_Criteria_Where('whitelabel_aff.is_active', true),
            new Model_Orm_Criteria_Where('whitelabel_aff.is_accepted', true),
            new Model_Orm_Criteria_Where('whitelabel_aff.token', mb_strtolower($token)),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('is_expired', false),
            new Model_Orm_Criteria_Where('whitelabel_user_id', $whitelabelUserId),
        ]);

        return $this->findOne();
    }

    public function findWhitelabelUserAffiliateByWhitelabelAff(int $whitelabelId, int $whitelabelUserId, int $whitelabelAffId): ?WhitelabelUserAff
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('whitelabel_user_id', $whitelabelUserId),
            new Model_Orm_Criteria_Where('whitelabel_aff_id', $whitelabelAffId),
            new Model_Orm_Criteria_Where('is_deleted', false),
            new Model_Orm_Criteria_Where('is_expired', false),
        ]);

        return $this->findOne();
    }

    public function insert(array $whitelabel, int $whitelabelUserId, int $affiliateId): WhitelabelUserAff
    {
        $userAffiliate = new WhitelabelUserAff;
        $userAffiliate->whitelabelId = $whitelabel['id'];
        $userAffiliate->whitelabelUserId = $whitelabelUserId;
        $userAffiliate->whitelabelAffId = $affiliateId;
        $userAffiliate->whitelabelAffMediumId = null;
        $userAffiliate->whitelabelAffCampaignId = null;
        $userAffiliate->whitelabelAffContentId = null;
        $userAffiliate->externalId = null;
        $userAffiliate->isDeleted = 0;
        $userAffiliate->isAccepted = 1;
        $userAffiliate->isExpired = 0;

        $userAffiliate->save();

        return $userAffiliate;
    }

    public function updateClickId(int $whitelabelId, int $whitelabelUserId, string $clickId): void
    {
        $this->db->update($this->model->get_table_name())
            ->set(['external_id' => $clickId])
            ->where('whitelabel_id', '=', $whitelabelId)
            ->and_where('whitelabel_user_id', '=', $whitelabelUserId)
            ->execute();
    }

    public function getUsersCountByAffiliateId(int $affiliateId, int $whitelabelId, ?string $startDate = null, ?string $endDate = null, bool $activeOnly = false): int
    {
        $criteria = [
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('whitelabel_aff_id', $affiliateId),
            new Model_Orm_Criteria_With_Relation('whitelabel_user'),
        ];

        if ($activeOnly) {
            $criteria[] = new Model_Orm_Criteria_Where('whitelabel_user.is_active', true);
        }

        $whereColumn = $activeOnly ? 'last_active' : 'date_register';
        if ($startDate) {
            $criteria[] = new Model_Orm_Criteria_Where('whitelabel_user.' . $whereColumn, $startDate, '>=');
        }

        if ($endDate) {
            $criteria[] = new Model_Orm_Criteria_Where('whitelabel_user.' . $whereColumn, $endDate, '<=');
        }

        $this->pushCriterias($criteria);

        return $this->getCount();
    }
}
