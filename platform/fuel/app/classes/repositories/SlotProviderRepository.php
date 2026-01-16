<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Repositories\Orm\AbstractRepository;
use Models\SlotProvider;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;

/**
 * @method findOneBySlug(string $slotProviderSlug)
 */
class SlotProviderRepository extends AbstractRepository
{
    public function __construct(SlotProvider $model)
    {
        parent::__construct($model);
    }

    public function findSlotProviderSlugById(int $slotProviderId): string
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['slug']),
            new Model_Orm_Criteria_Where('id', $slotProviderId)
        ]);

        /** @var SlotProvider $slotProvider */
        $slotProvider = $this->findOne();

        return $slotProvider ? $slotProvider->slug : '';
    }
}
