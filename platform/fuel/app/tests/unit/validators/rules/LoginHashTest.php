<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Validation;
use Validators\Rules\LoginHash;

class LoginHashTest extends Test_Unit
{
    /** @test
     * @dataProvider providerTestCases
     * @param mixed $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('loginHash' . uniqid());
        $inputData = [
            'loginHash' => $inputToValidate,
        ];

        $rule = new LoginHash('loginHash', 'Login hash');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['mrclqymfmvcvjkldunjeopigndmpxugkbdilajljbcspghjrtvbzpukjukjcunun', true],
            ['mrclqymfmvcvjkldunjeopigndmpxugkbdilajljbcspghjrtvbzpukjukjcun+/', false],
            ['ABC123mfmvcvjkldunjeopigndmpxugkbdilajljbcspghjrtvbzpukjukjcunun', true],
            ['mrclqymfmvcvjkldunjeopigndmpxugkbdilajljbcspghjrtvbzpukjukjcun"!', false],
            ["mrclqymfmvcvjkldunjeopigndmp#ugkbdilajljbcspghjrtvbzpukjukjcun'!", false],
            ['/', false],
            ['.', false],
            ['', false],
            [null, false],
            ['@', false],
            ['mrclqymfmvcvjkldunjeopigndmpxugkbdilajljbcspghjrtvbzpukjukjcununsadaxasdasd', false], // too long
        ];
    }
}
