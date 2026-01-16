<?php

class Tests_Feature_Classes_Factory_Orm_Currency extends Test_Feature
{
    public function test_it_creates_random_currency(): void
    {
        $currency = Factory_Orm_Currency::forge()->build();
        $this->assertInstanceOf(Currency::class, $currency);
    }

    public function test_it_creates_random_currency_by_invoke(): void
    {
        $currency = new Factory_Orm_Currency;
        $this->assertInstanceOf(Currency::class, $currency());
    }

    public function test_it_creates_without_saving_currency_by_invoke(): void
    {
        $callable = new Factory_Orm_Currency;
        $currency = $callable(false);
        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertEmpty($currency->id);
    }

    public function test_it_creates_usd_currency(): void
    {
        $currency = Factory_Orm_Currency::as_usd()->build();
        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertSame($currency->code, 'USD');
    }

    public function test_it_gives_the_same_instance_of_existing_resource_if_exists(): void
    {
        $currency1 = Factory_Orm_Currency::as_usd()->build();
        $this->assertInstanceOf(Currency::class, $currency1);
        $this->assertSame($currency1->code, 'USD');

        $currency2 = Factory_Orm_Currency::as_usd()->build();
        $this->assertInstanceOf(Currency::class, $currency2);
        $this->assertSame($currency2->code, 'USD');
        $this->assertSame($currency1->id, $currency2->id);
    }
}
