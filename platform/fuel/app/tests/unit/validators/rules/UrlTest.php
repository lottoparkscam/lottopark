<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Fuel\Core\Validation;
use Validators\Rules\Url;

final class UrlTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider providerTestCases
     * @param mixed|int|string|null  $inputToValidate
     * @param bool  $expectedResult
     */
    public function singleFieldPasses($inputToValidate, bool $expectedResult): void
    {
        $validation = Validation::forge('url' . uniqid());
        $inputData = [
            'url' => $inputToValidate,
        ];

        $rule = new Url('url', 'Url');
        $rule->setValidation($validation);
        $rule->applyRules();

        $this->assertEquals($expectedResult, $validation->run($inputData));
    }

    public static function providerTestCases(): array
    {
        return [
            ['https://api.lottopark.loc/api/internal/seoWidgets?test=a&test=b', true],
            ['https://api.lottopark.loc/api/internal/seoWidgets?test[0]=a', true],
            ['https://api.lottopark.loc/api/internal/seoWidgets?testa,b,c', true],
            ['asd', false],
            ['/test/pl', false],
            ['api.lottopark.loc/api/test/', false],
            ['api.lottopark.loc/api/test', false],
        ];
    }
}
