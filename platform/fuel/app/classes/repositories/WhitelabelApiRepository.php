<?php

namespace Repositories;

use Repositories\Orm\AbstractRepository;
use Models\WhitelabelApi;

/**
 * Class WhitelabelApiRepository
 */
class WhitelabelApiRepository  extends AbstractRepository
{
    public function __construct(WhitelabelApi $model)
    {
        parent::__construct($model);
    }
}
