<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Limit;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Repositories\Orm\AbstractRepository;
use Models\SlotLog;

/** @method deleteRecordsOlderThanXDays(int $days, string $dateColumn = 'date'): void */
class SlotLogRepository extends AbstractRepository
{
    public function __construct(SlotLog $model)
    {
        parent::__construct($model);
    }

    public function findLastSucceedInitByWhitelabelId(int $whitelabelId): ?SlotLog
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('whitelabel_slot_provider'),
            new Model_Orm_Criteria_Where('action', SlotLog::ACTION_INIT),
            new Model_Orm_Criteria_Where('is_error', false),
            new Model_Orm_Criteria_Where('whitelabel_slot_provider.whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Order('created_at', 'desc'),
            new MOdel_Orm_Criteria_Limit(1),
        ]);

        return $this->findOne();
    }

    public function findLastSucceedLogByWhitelabelId(int $whitelabelId): ?SlotLog
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('whitelabel_slot_provider'),
            new Model_Orm_Criteria_Where('is_error', false),
            new Model_Orm_Criteria_Where('whitelabel_slot_provider.whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Order('created_at', 'desc'),
            new MOdel_Orm_Criteria_Limit(1),
        ]);

        return $this->findOne();
    }
}
