<?php

namespace Unit\Classes\Models;

use Container;
use Test_Unit;

class IssetTest extends Test_Unit
{
    /** @test */
    public function coalesce(): void
    {
        $whitelabel = Container::get('whitelabel');
        $expected = 'abc';
        $whitelabel->supportEmail = $expected;

        $this->assertSame($expected, $whitelabel->supportEmail);
        $this->assertSame($expected, $whitelabel->support_email);
        $this->assertSame($expected, $whitelabel->supportEmail ?? 'test');
        $this->assertSame($expected, $whitelabel->support_email ?? 'test');
    }
}
