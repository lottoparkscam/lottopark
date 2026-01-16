<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Models\Currency;
use Repositories\Orm\AbstractRepository;

/**
 * @method Currency findOneByCode(string $code)
 * @method Currency findOneById(int $id)
 */
class CurrencyRepository extends AbstractRepository
{
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }

    public function getAllCodes(): array
    {
        $this->pushCriteria(new Model_Orm_Criteria_Select(['code']));
        return $this->getResultsForSingleField('code');
    }
}
