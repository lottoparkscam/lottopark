<?php

namespace Tests;

use Helpers\StringHelper;
use Test_Unit;

class StringTest extends Test_Unit
{
    /** @test */
    public function removeLastChunkBySeparator(): void
    {
        $this->assertSame('a.b.c', StringHelper::removeLastChunkBySeparator('a.b.c.d', '.'));
        $this->assertSame('ab-cd-ef', StringHelper::removeLastChunkBySeparator('ab-cd-ef-gh', '-'));
        $this->assertSame('Delete last', StringHelper::removeLastChunkBySeparator('Delete last word', ' '));
    }

    /** @test */
    public function classnameMinusNamespace(): void
    {
        $this->assertSame('StringHelper', StringHelper::classnameMinusNamespace('Validators\Rules\StringHelper'));
        $this->assertSame('StringHelper', StringHelper::classnameMinusNamespace('StringHelper'));
    }
}
