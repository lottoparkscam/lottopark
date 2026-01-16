<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Validation;
use Validators\Rules\ClickId;

class ClickIdTest extends Test_Unit
{
    /** @test
     * Prefixed Token - 3 letters + numbers
     * Example: LPD123456789
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('register_clickID' . uniqid());
        $inputData = [
            'clickID' => $inputToValidate,
        ];

        $rule = new ClickId('clickID', 'register token');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['ss', true],
            ['LPP123456789', true],
            ['', false],
            [null, false],
            ['123LPD', true],
            ['LPP12345L', true],
            ['LPD12#3$', false],
            ['LP12345/', false],
            ['P12345^', false],
            ['P1^sadasdsW2345^', false],
        ];
    }
}
