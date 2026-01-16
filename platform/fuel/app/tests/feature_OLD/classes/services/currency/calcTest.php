<?php

/**
 * Helper test for testing ORM.
 */
class Tests_Feature_Services_Currency_Calc extends Test_Feature
{
    /** @var Services_Currency_Calc */
    private $currency_calc;

    public function setUp(): void
    {
        parent::setUp();
        $this->currency_calc = Container::get(Services_Currency_Calc::class);
    }

    public function test_it_converts_amounts(): void
    {
        $amount = $this->currency_calc->convert_to_any(1, 'EUR', 'PLN');
        $this->assertNotSame(1, $amount);
        $this->assertIsFloat($amount);
    }
}
