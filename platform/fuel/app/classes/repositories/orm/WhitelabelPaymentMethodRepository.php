<?php

namespace Repositories\Orm;

use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Models\WhitelabelPaymentMethod;

/** @method WhitelabelPaymentMethod findOneById(int $paymentMethodId) */
class WhitelabelPaymentMethodRepository extends AbstractRepository
{
    public function __construct(WhitelabelPaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function getByWhitelabel(int $id, array $relations = []): array
    {
        $this->handleRelations($relations);

        $this->model->push_criteria(new Model_Orm_Criteria_Where('whitelabel_id', $id));

        return $this->model->get_results();
    }

    private function handleRelations(array $relations = []): void
    {
        foreach ($relations as $relation) {
            $this->model->push_criteria(new Model_Orm_Criteria_With_Relation($relation));
        }
    }

    public function getDataJsonByPaymentIdAndWhitelabelId(int $paymentMethodId, int $whitelabelId): ?array
    {
        $this->model->push_criterias([
            new Model_Orm_Criteria_Select(['data_json']),
            new Model_Orm_Criteria_Where('payment_method_id', $paymentMethodId),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId)
        ]);

        return $this->model->find_one()['data_json'] ?? [];
    }
}
