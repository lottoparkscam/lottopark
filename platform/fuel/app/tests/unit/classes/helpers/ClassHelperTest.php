<?php

namespace Tests\Unit\Classes\Helpers;

use Fieldset;
use Fuel\Core\Cache;
use Helpers\ClassHelper;
use Test_Unit;
use Tests\Feature\Helpers\Raffle\RaffleMailerTest;

class ClassHelperTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider classProvider
     */
    public function getClassNameWithoutNamespace(string|object $class, string $expected): void
    {
        $result = ClassHelper::getClassNameWithoutNamespace($class);
        $this->assertEquals($expected, $result);
    }

    public function classProvider(): array
    {
        return [
            [get_class(new Fieldset()), 'Fieldset'],
            [get_class(new Cache()), 'Cache'],
            [new RaffleMailerTest(), 'RaffleMailerTest'],
        ];
    }
}
