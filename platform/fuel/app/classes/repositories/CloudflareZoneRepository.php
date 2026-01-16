<?php

namespace Repositories;

use Models\CloudflareZone;
use Repositories\Orm\AbstractRepository;

class CloudflareZoneRepository extends AbstractRepository
{
    public function __construct(CloudflareZone $model)
    {
        parent::__construct($model);
    }
}
