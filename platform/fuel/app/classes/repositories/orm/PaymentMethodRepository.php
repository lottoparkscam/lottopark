<?php

namespace Repositories\Orm;

use Classes\Orm\Criteria\By\Model_Orm_Criteria_By_Id;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Models\PaymentMethod;

class PaymentMethodRepository
{
    private PaymentMethod $model;

    public function __construct(PaymentMethod $model)
    {
        $this->model = $model;
    }

    public function save(PaymentMethod $paymentMethod, bool $flush = true): void
    {
        $paymentMethod->save();
    }

    public function getById(int $id, array $relations = []): PaymentMethod
    {
        foreach ($relations as $relation) {
            $this->model->push_criteria(new Model_Orm_Criteria_With_Relation($relation));
        }

        $this->model->push_criteria(new Model_Orm_Criteria_By_Id($id));

        return $this->model->get_one();
    }
}
