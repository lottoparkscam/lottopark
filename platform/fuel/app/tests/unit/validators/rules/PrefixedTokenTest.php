<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Validation;
use Validators\Rules\PrefixedToken;

class PrefixedTokenTest extends Test_Unit
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
        $validation = Validation::forge('prefixed_token' . uniqid());
        $inputData = [
            'token' => $inputToValidate,
        ];

        $rule = new PrefixedToken('token', 'Transaction token');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['LPD12345678', true],
            ['LPP123456789', true],
            ['', false],
            [null, false],
            ['123LPD', false],
            ['LPP12345L', false],
            ['LPD12#3$', false],
            ['LP12345', false],
            ['P12345', false],
        ];
    }
}
