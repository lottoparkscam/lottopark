<?php

namespace Repositories;

use Repositories\Orm\AbstractRepository;
use Models\WhitelabelApiIp;

class WhitelabelApiIpRepository  extends AbstractRepository
{
    public function __construct(WhitelabelApiIp $model)
    {
        parent::__construct($model);
    }
}
