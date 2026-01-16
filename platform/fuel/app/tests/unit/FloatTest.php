<?php

namespace Tests\Unit;

use Test_Unit;

class FloatTest extends Test_Unit
{
    public function setUp(): void
    {
        parent::setUp();
        putenv('LC_ALL=pl_PL.utf8');
        setlocale(LC_ALL, 'pl_PL.utf8');
    }

    /** @test */
    public function castFunctionReturn(): void
    {
        function test(): string
        {
            return 4.96;
        }

        $this->assertSame('4.96', test());

        putenv('LC_ALL=en_GB.utf8');
        setlocale(LC_ALL, 'en_GB.utf8');
    }
}
