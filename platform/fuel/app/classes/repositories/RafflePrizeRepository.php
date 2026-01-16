<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Helpers_General;
use Models\RafflePrize;
use Repositories\Orm\AbstractRepository;

class RafflePrizeRepository extends AbstractRepository
{
    public function __construct(RafflePrize $model)
    {
        parent::__construct($model);
    }

    public function getMainPrizesByDrawId(int $drawId): array
    {
        return $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('tier'),
            new Model_Orm_Criteria_With_Relation('lines'),
            new Model_Orm_Criteria_With_Relation('currency'),

            new Model_Orm_Criteria_Where('lines.status', Helpers_General::TICKET_STATUS_WIN),
            new Model_Orm_Criteria_Where('raffle_draw_id', $drawId),
            new Model_Orm_Criteria_Where('tier.is_main_prize', true),
            new Model_Orm_Criteria_Order('per_user', 'desc'),
        ])->getResults() ?? [];
    }

    public function getPrizesByDraw(int $drawId): array
    {
        return $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('tier'),
            new Model_Orm_Criteria_With_Relation('lines'),
            new Model_Orm_Criteria_With_Relation('currency'),

            new Model_Orm_Criteria_Where('lines.status', Helpers_General::TICKET_STATUS_WIN),
            new Model_Orm_Criteria_Where('raffle_draw_id', $drawId),
            new Model_Orm_Criteria_Order('per_user', 'desc')
        ])->getResults() ?? [];
    }
}
