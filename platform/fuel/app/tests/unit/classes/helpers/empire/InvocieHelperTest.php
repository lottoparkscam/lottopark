<?php

namespace Tests\Unit\Classes\Helpers\empire;

use Helpers\empire\InvoiceHelper;
use Test_Unit;

class InvoiceHelperTest extends Test_Unit
{
    /** @test */
    public function calculateIncome(): void
    {
        $this->assertSame(4.0, InvoiceHelper::calculateIncome(2.34, 1.66));
    }

    /** @test */
    public function calculateRoyalties(): void
    {
        $this->assertSame(4.0, InvoiceHelper::calculateRoyalties(2.34, 1.66));
    }
}
