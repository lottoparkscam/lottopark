<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Fuel\Core\Validation;
use Validators\Rules\LotteryNumber;

final class LotteryNumberTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('number' . uniqid());
        $inputData = [
            'number' => $inputToValidate,
        ];

        $rule = new LotteryNumber('number', 'number');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['1', true],
            ['99', true],
            ['0', false],
            [1, true],
            [99, true],
            [0, false],
            ['a', false],
            ['!', false],
            [null, false],
        ];
    }
}
