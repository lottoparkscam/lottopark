<?php

namespace Repositories\Orm;

use Models\WhitelabelRaffleTicket;
use Stwarog\UowFuel\FuelEntityManager;

class TicketRepository
{
    private WhitelabelRaffleTicket $model;
    private FuelEntityManager $entityManager;

    public function __construct(WhitelabelRaffleTicket $model, FuelEntityManager $entityManager)
    {
        $this->model = $model;
        $this->entityManager = $entityManager;
    }

    public function save(WhitelabelRaffleTicket $ticket, bool $flush = true): void
    {
        $this->entityManager->save($ticket, $flush);
    }

    public function saveManyAndFlush(WhitelabelRaffleTicket ...$tickets): void
    {
        foreach ($tickets as $ticket) {
            $this->entityManager->save($ticket);
        }
        $this->entityManager->flush();
    }
}
