<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Validation;
use Validators\Rules\Amount;

class AmountTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('balance_amount' . uniqid());
        $inputData = [
            'amount' => $inputToValidate,
        ];

        $rule = new Amount('amount', 'Amount');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            [45.01, true],
            [45, true],
            [45.101, true],
            [0.90, true],
            ["45.01", true],
            ["45,01", false],
            ["NaN", false],
            [null, false],
            ["", false],
        ];
    }
}
