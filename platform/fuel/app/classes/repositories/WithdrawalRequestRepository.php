<?php

namespace Repositories;

use Classes\Orm\AbstractOrmModel;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;
use Models\WithdrawalRequest;

class WithdrawalRequestRepository  extends AbstractRepository
{
    public function __construct(WithdrawalRequest $model)
    {
        parent::__construct($model);
    }

    public function findByTokenForWhitelabel(string $token, int $whitelabelId): ?AbstractOrmModel
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('token', $token),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId)
        ]);
        return $this->findOne();
    }
}
