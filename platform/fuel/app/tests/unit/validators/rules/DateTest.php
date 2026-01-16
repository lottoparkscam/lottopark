<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Fuel\Core\Validation;
use Validators\Rules\Date;

final class DateTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('date' . uniqid());
        $inputData = [
            'date' => $inputToValidate,
        ];

        $rule = new Date('date', 'date');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['2019-02-03', true],
            ['2019-02-40', false],
            ['2019', true],
            ['02-01-1997', true],
            ['02-01-1997!', false],
        ];
    }
}
