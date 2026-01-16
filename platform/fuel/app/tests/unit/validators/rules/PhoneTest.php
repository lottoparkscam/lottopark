<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Fuel\Core\Validation;
use Validators\Rules\Phone;

final class PhoneTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('phone' . uniqid());
        $inputData = [
            'phone' => $inputToValidate,
        ];

        $rule = new Phone('phone', 'Phone');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['123123123', true],
            ['+48123123123', true],
            ['+48-123 123 123', true],
            ['+48 123-123-123', true],
            ['+48 123 123 123', true],
            ['+48 123-123-123', true],
            ['+48-123-123-123', true],

            // max according to libphonenumber external package
            ['12345678901234567', true], // max 17 chars
            // min according to google
            ['1234', true], // min 4 chars

            //fails
            ['+48', false],
            ['+48-123-123-123-123123123123123123', false], // max number length is 17
            ['123456789012345678', false], // 18 chars

            // check passing strings
            ['asd', false],
            ['asdbcd', false],
            ['asdbcdefg', false],
            ['asd bcd efg', false],

            // this rule is optional, so we should check if it passes empty input
            [null, true],
            ['', true],
        ];
    }
}
