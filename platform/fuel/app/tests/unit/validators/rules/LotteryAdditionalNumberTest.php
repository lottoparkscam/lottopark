<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Fuel\Core\Validation;
use Validators\Rules\LotteryAdditionalNumber;

final class LotteryAdditionalNumberTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('additionalNumber' . uniqid());
        $inputData = [
            'additionalNumber' => $inputToValidate,
        ];

        $rule = new LotteryAdditionalNumber('additionalNumber', 'additionalNumber');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['0', true],
            ['1', true],
            ['9', true],
            ['-1', false],
            ['10', false],
            [0, true],
            [1, true],
            [9, true],
            [-1, false],
            [10, false],
        ];
    }
}
