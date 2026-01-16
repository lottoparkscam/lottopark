<?php

namespace Repositories;

use Models\WhitelabelLtech;
use Repositories\Orm\AbstractRepository;

/**
 * @method WhitelabelLtech|null findByIsEnabled(bool $true)
 */
class WhitelabelLtechRepository extends AbstractRepository
{
    public function __construct(WhitelabelLtech $model)
    {
        parent::__construct($model);
    }
}
