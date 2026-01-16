<?php

namespace Repositories;

use Models\CrmLog;
use Repositories\Orm\AbstractRepository;

class CrmLogRepository extends AbstractRepository
{
    public function __construct(CrmLog $model)
    {
        parent::__construct($model);
    }
}
