<?php

namespace Repositories;

use Models\SocialType;
use Repositories\Orm\AbstractRepository;

/**
 * @method findOneByType(string $type)
 */
class SocialTypeRepository extends AbstractRepository
{
    public function __construct(SocialType $model)
    {
        parent::__construct($model);
    }
}
