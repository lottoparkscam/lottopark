<?php

namespace Repositories;

use Repositories\Orm\AbstractRepository;
use Models\WhitelabelAffGroup;

/** @method WhitelabelAffGroup findOneById(int $id) */
class WhitelabelAffGroupRepository extends AbstractRepository
{
    public function __construct(WhitelabelAffGroup $model)
    {
        parent::__construct($model);
    }
}
