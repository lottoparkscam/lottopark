<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Validation;
use Validators\Rules\Email;

class EmailTest extends Test_Unit
{
    /** @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('email' . uniqid());
        $inputData = [
            'user_email' => $inputToValidate,
        ];

        $rule = new Email('user_email', 'User Email');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['contact@lotto.com', true],
            ['//contact@lotto.com//', false],
            ['contact', false],
            ['', false],
            [null, false],
            ['test@', false],
            ['test@test', false],
        ];
    }
}
