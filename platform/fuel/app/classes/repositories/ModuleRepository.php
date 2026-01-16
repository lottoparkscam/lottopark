<?php

namespace Repositories;

use Classes\Orm\AbstractOrmModel;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;
use Models\Module;

class ModuleRepository  extends AbstractRepository
{
    public function __construct(Module $model)
    {
        parent::__construct($model);
    }

    public function findByName(string $name): AbstractOrmModel
    {
        return $this->pushCriteria(
            new Model_Orm_Criteria_Where('name', $name)
        )->getOne();
    }
}
