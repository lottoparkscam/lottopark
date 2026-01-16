<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\SlotWhitelistIp;
use Repositories\Orm\AbstractRepository;

class SlotWhitelistIpRepository extends AbstractRepository
{
    public function __construct(SlotWhitelistIp $model)
    {
        parent::__construct($model);
    }

    /** @return string[] */
    public function getAllowedIpsForSlotProvider(int $slotProviderId): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['ip']),
            new Model_Orm_Criteria_Where('slot_provider_id', $slotProviderId),
        ]);

        return $this->getResultsForSingleField();
    }
}
