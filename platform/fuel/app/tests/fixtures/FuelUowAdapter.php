<?php

namespace Tests\Fixtures;

use Exception;
use Orm\Model;
use Stwarog\FuelFixtures\Fuel\PersistenceContract;
use Stwarog\UowFuel\FuelEntityManager;

final class FuelUowAdapter implements PersistenceContract
{
    private FuelEntityManager $fuelEntityManager;

    public function __construct(FuelEntityManager $fuelEntityManager)
    {
        $this->fuelEntityManager = $fuelEntityManager;
    }

    /**
     * @throws Exception
     */
    public function persist(Model ...$models): void
    {
        foreach ($models as $model) {
            $this->fuelEntityManager->save($model);
        }
        $this->fuelEntityManager->flush();
    }
}
