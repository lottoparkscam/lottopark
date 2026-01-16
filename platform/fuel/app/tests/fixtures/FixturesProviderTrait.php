<?php

namespace Tests\Fixtures;

use Generator;
use Orm\Model;

trait FixturesProviderTrait
{
    /**
     * @return Generator<Model>
     */
    public function provideFixture(): Generator
    {
        self::setUp();
        foreach ($this->container->get('fixtures') as $fixtureName) {
            yield $fixtureName => [$this->container->get($fixtureName)];
        }
    }
}
