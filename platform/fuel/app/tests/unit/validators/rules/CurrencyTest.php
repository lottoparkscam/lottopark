<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Fuel\Core\Validation;
use Validators\Rules\Currency;

final class CurrencyTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('currency' . uniqid());
        $inputData = [
            'currency' => $inputToValidate,
        ];

        $rule = new Currency('currency', 'Currency');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['PLN', true],
            ['USD', true],
            ['EUR', true],
            ['EU', false],
            ['E', false],
            ['ABCD', false],
        ];
    }
}
