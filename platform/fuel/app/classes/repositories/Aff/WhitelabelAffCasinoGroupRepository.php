<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Fuel\Core\Database_Query_Builder;
use Repositories\Orm\AbstractRepository;
use Models\WhitelabelAffCasinoGroup;

class WhitelabelAffCasinoGroupRepository extends AbstractRepository
{
    public function __construct(WhitelabelAffCasinoGroup $whitelabelAffCasinoGroup)
    {
        parent::__construct($whitelabelAffCasinoGroup);
    }

    public function getGroupsByWhitelabelId(int $whitelabelId): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select([
                'id',
                'name',
                'commission_percentage_value_for_tier_1',
                'commission_percentage_value_for_tier_2',
            ]),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
        ]);

        return $this->getResults();
    }

    public function findGroupByWhitelabelIdAndGroupId(
        int $whitelabelId,
        int $groupId
    ): array {
        /** @var Database_Query_Builder $query */
        $query = $this->db
            ->selectArray([
                'id',
                'name',
                'commission_percentage_value_for_tier_1',
                'commission_percentage_value_for_tier_2',
            ])
            ->from(WhitelabelAffCasinoGroup::get_table_name())
            ->where('whitelabel_id', $whitelabelId)
            ->and_where('id', $groupId)
            ->execute();

        return $query->as_array()[0] ?? [];
    }

    public function updateCommissionValuesForGroup(
        int $groupId,
        int $whitelabelId,
        float $commissionPercentageValueForTier1,
        float $commissionPercentageValueForTier2
    ): void {
        $this->db->update(WhitelabelAffCasinoGroup::get_table_name())
            ->set([
                'commission_percentage_value_for_tier_1' => $commissionPercentageValueForTier1,
                'commission_percentage_value_for_tier_2' => $commissionPercentageValueForTier2
            ])
            ->where('id', $groupId)
            ->where('whitelabel_id', $whitelabelId)
            ->execute();
    }

    public function create(
        int $whitelabelId,
        string $groupName,
        float $commissionPercentageValueForTier1,
        float $commissionPercentageValueForTier2
    ): void {
        $group = new WhitelabelAffCasinoGroup();

        $group->name = $groupName;
        $group->whitelabelId = $whitelabelId;
        $group->commissionPercentageValueForTier1 = $commissionPercentageValueForTier1;
        $group->commissionPercentageValueForTier2 = $commissionPercentageValueForTier2;

        $group->save();
    }
}
