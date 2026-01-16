<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Validation;
use Validators\Rules\Login;

class LoginTest extends Test_Unit
{
    /** @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('login' . uniqid());
        $inputData = [
            'user_login' => $inputToValidate,
        ];

        $rule = new Login('user_login', 'User Login');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['user', true],
            ['test@test', true],
            ['test@', true],
            [null, false],
            ['', false],
            ['as', false], // too short
            ['h9My9h0oDiIu73sEeGvq8jFWx70qTqe9wZ3QesIRrfgRIi4EMnJ6AdAJTkjbkjbkjbkjCUa5r9b3MM5YQiA4iBzMYdZFn0EcgKjkI7eYO2UEYbX', false] // too long
        ];
    }
}
