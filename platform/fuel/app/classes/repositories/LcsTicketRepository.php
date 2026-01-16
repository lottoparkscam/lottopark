<?php

namespace Repositories;

use Repositories\Orm\AbstractRepository;
use Models\LcsTicket;

/**
 * @method LcsTicket|null findOneByWhitelabelUserTicketSlipId(int $id)
 */
class LcsTicketRepository extends AbstractRepository
{
    public function __construct(LcsTicket $model)
    {
        parent::__construct($model);
    }
}
