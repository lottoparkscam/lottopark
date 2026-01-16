<?php

namespace Tests\Unit\Validators\Rules;

use Exception;
use Test_Unit;
use Validators\Rules\Amount;
use Validators\Rules\CurrencyCode;

class RuleTest extends Test_Unit
{
    /** @test */
    public function addRule_ThrowsExceptionWhenFieldIsNotDefined(): void
    {
        $rule = Amount::build();
        $this->expectException(Exception::class);
        $rule->addRule('numeric_between', 0, 10); // This scenario means that even if addRule is executed, it does completely NOTHING and rule is not applied
    }

    /** @test */
    public function build_CorrectlyBuildsDefaultRuleWithNameAndLabel(): void
    {
        $rule = Amount::build();
        $this->assertEquals('amount', $rule->getName());
        $this->assertEquals('Amount', $rule->getLabel());

        $rule = CurrencyCode::build();
        $this->assertEquals('currency_code', $rule->getName());
        $this->assertEquals('Currency Code', $rule->getLabel());
    }

    /** @test */
    public function build_CorrectlyBuildsWithSpecifiedNameAndLabel(): void
    {
        $rule = Amount::build('amount_balance', 'Amount Balance');
        $this->assertEquals('amount_balance', $rule->getName());
        $this->assertEquals('Amount Balance', $rule->getLabel());

        $rule = CurrencyCode::build('specified_currency', 'Specified Currency');
        $this->assertEquals('specified_currency', $rule->getName());
        $this->assertEquals('Specified Currency', $rule->getLabel());
    }

    /** @test */
    public function build_MissingFields_CorrectlyGenerates(): void
    {
        $rule = Amount::build('custom_balance');
        $this->assertEquals('Custom Balance', $rule->getLabel());

        $rule = Amount::build('amount_debit', '');
        $this->assertEquals('Amount Debit', $rule->getLabel());

        $rule = Amount::build('', '');
        $this->assertEquals('amount', $rule->getName());
        $this->assertEquals('Amount', $rule->getLabel());
    }

    /** @test */
    public function build_ReturnsCorrectClassInstance(): void
    {
        $rule = Amount::build();
        $this->assertInstanceOf(Amount::class, $rule);
        $rule = CurrencyCode::build();
        $this->assertInstanceOf(CurrencyCode::class, $rule);
    }
}
