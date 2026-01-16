<?php

namespace Repositories\Orm;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Models\Whitelabel;

/**
 * @method findOneByDomain(string $domain)
 * @method findOneById(int $id)
 */
class WhitelabelRepository extends AbstractRepository
{
    public function __construct(Whitelabel $model)
    {
        parent::__construct($model);
    }

    public function findByDomain(string $domain): ?Whitelabel
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = $this->pushCriterias([
            new Model_Orm_Criteria_Where('domain', $domain)
        ])->findOne();

        return $whitelabel;
    }

    public function countV2(): int
    {
        $this->pushCriteria(new Model_Orm_Criteria_Where('type', Whitelabel::TYPE_V2));
        return $this->getCount();
    }

    public function countWithEnabledSlots(): int
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('whitelabel_slot_providers'),
            new Model_Orm_Criteria_Where('whitelabel_slot_providers.id', null, 'IS NOT')
        ]);

        return $this->getCount();
    }
}
