<?php

namespace Repositories;

use Models\WhitelabelUserTicketSlip;
use Repositories\Orm\AbstractRepository;

/**
 * @method findOneById(int $id)
 */
class WhitelabelUserTicketSlipRepository extends AbstractRepository
{
    public function __construct(WhitelabelUserTicketSlip $model)
    {
        parent::__construct($model);
    }
}
