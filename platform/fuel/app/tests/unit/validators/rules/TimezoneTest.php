<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Fuel\Core\Validation;
use Validators\Rules\Timezone;

final class TimezoneTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('timezone' . uniqid());
        $inputData = [
            'timezone' => $inputToValidate,
        ];

        $rule = new Timezone('timezone', 'Timezone');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['123', true],
            ['Europe/Warsaw', true],
            ['Europe\Warsaw', false],
            ['America/Puerto_Rico', true],
            ['America/North_Dakota/New_Salem', true],
            ['Etc/GMT@-11', false],
        ];
    }
}
