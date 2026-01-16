<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;
use Models\WhitelabelAffCampaign;

class WhitelabelAffCampaignRepository extends AbstractRepository
{
    public function __construct(WhitelabelAffCampaign $model)
    {
        parent::__construct($model);
    }

    /** @return WhitelabelAffCampaign[] */
    public function getCampaigns(int $whitelabelAffId): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_aff_id', $whitelabelAffId),
            new Model_Orm_Criteria_Order('campaign')
        ]);

        return $this->getResults() ?? [];
    }
}
