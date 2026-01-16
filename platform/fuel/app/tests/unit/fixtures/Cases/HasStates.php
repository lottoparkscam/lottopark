<?php

namespace Unit\Fixtures\Cases;

use Stwarog\FuelFixtures\Fuel\Factory;

interface HasStates
{
    /**
     * Given fixture<ConcreteModel>
     * When getStates is called
     * Then all states names should be as expected
     * @return mixed|void
     */
    public function getStates_ContainsExpectedStates(Factory $fixture);
}
