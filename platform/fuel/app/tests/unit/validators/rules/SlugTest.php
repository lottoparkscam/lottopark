<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Fuel\Core\Validation;
use Validators\Rules\Slug;

final class SlugTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('slug' . uniqid());
        $inputData = [
            'slug' => $inputToValidate,
        ];

        $rule = new Slug('slug', 'Slug');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['abcD-123', true],
            ['123', true],
            ['abcD_123', true],
            ['abcD-123!', false],
            ['a', false],
            ['abc 123 Test -', false],
            ['!@#$%^', false],
        ];
    }
}
