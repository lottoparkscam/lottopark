<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Validation;
use Validators\Rules\Name;

class NameTest extends Test_Unit
{
    /** @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('name' . uniqid());
        $inputData = [
            'name' => $inputToValidate,
        ];

        $rule = new Name('name', 'Name');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['name', true],
            ['马尔桑', true],
            ['Järvi-Latva-Kiikert', true],
            [null, false],
            ['', false],
        ];
    }
}
