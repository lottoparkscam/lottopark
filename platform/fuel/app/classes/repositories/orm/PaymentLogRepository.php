<?php

namespace Repositories\Orm;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Models\PaymentLog;
use Orm\RecordNotFound;
use Repositories\Traits\DeleteRecordsOlderThanXDaysTrait;
use Wrappers\Db;
use Container;

/** @method deleteRecordsOlderThanXDays(int $days, string $dateColumn = 'date'): void */
class PaymentLogRepository
{
    use DeleteRecordsOlderThanXDaysTrait;

    protected Db $db;

    private PaymentLog $model;

    public function __construct(PaymentLog $model)
    {
        $this->model = $model;
        $this->db = Container::get(Db::class);
    }

    /**
     * @param int $id
     * @param array $relations
     *
     * @return PaymentLog
     *
     * @throws RecordNotFound
     */
    public function getById(int $id, array $relations = []): PaymentLog
    {
        foreach ($relations as $relation) {
            $this->model->push_criteria(
                new Model_Orm_Criteria_With_Relation($relation)
            );
        }

        $this->model->push_criteria(
            new Model_Orm_Criteria_Where('id', $id)
        );

        return $this->model->get_one();
    }

    public function save(PaymentLog $log, bool $flush = true): void
    {
        $log->save();
    }
}
