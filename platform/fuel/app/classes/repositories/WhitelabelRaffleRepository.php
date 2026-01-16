<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Container;
use Models\Whitelabel;
use Models\WhitelabelRaffle;
use Repositories\Orm\AbstractRepository;

class WhitelabelRaffleRepository extends AbstractRepository
{
    public function __construct(WhitelabelRaffle $model)
    {
        parent::__construct($model);
    }

    public function findOneByRaffleIdForCurrentWhitelabel(int $raffleId): ?WhitelabelRaffle
    {
        /** @var ?Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        if (empty($whitelabel)) {
            return null;
        }

        $this->pushCriterias([
            new Model_Orm_Criteria_Where('raffle_id', $raffleId),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabel->id),
        ]);

        return $this->findOne();
    }
}
