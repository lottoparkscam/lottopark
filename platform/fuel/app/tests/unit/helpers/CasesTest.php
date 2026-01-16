<?php

use Helpers\CaseHelper;

class CasesTest extends Test_Unit
{
    public const EXAMPLE_CAMEL_CASE = 'thisIsTest';
    public const EXAMPLE_SNAKE_CASE = 'this_is_test';
    public const EXAMPLE_PASCAL_CASE = 'ThisIsTest';
    public const EXAMPLE_KEBAB_CASE = 'this-is-test';
    public const EXAMPLE_TITLE_CASE = 'Amount Balance';
    public const EXAMPLE_CAMEL_CASE_WITH_NUMBERS = 'thisIs2Test2';
    public const EXAMPLE_SNAKE_CASE_WITH_NUMBERS = 'this_is_2_test_2';
    public const EXAMPLE_PASCAL_CASE_WITH_NUMBERS = 'ThisIs2Test2';
    public const EXAMPLE_KEBAB_CASE_WITH_NUMBERS = 'this-is-2-test-2';

    /** @test */
    public function camelToSnake(): void
    {
        $textInSnake = CaseHelper::camelToSnake(self::EXAMPLE_CAMEL_CASE);
        $this->assertSame(self::EXAMPLE_SNAKE_CASE, $textInSnake);

        $textInSnake = CaseHelper::camelToSnake(self::EXAMPLE_CAMEL_CASE_WITH_NUMBERS);
        $this->assertSame(self::EXAMPLE_SNAKE_CASE_WITH_NUMBERS, $textInSnake);
    }

    /** @test */
    public function camelToSnake_trTRUtf8(): void
    {
        putenv('LC_ALL=tr_TR.utf8');
        setlocale(LC_ALL, 'tr_TR.utf8');

        $textInSnake = CaseHelper::camelToSnake('languageId');
        $this->assertSame('language_id', $textInSnake);

        putenv('LC_ALL=en_GB.utf8');
        setlocale(LC_ALL, 'en_GB.utf8');
    }

    /** @test */
    public function snakeToCamel(): void
    {
        $textInCamel = CaseHelper::snakeToCamel(self::EXAMPLE_SNAKE_CASE);
        $this->assertSame(self::EXAMPLE_CAMEL_CASE, $textInCamel);

        $textInCamel = CaseHelper::snakeToCamel(self::EXAMPLE_SNAKE_CASE_WITH_NUMBERS);
        $this->assertSame(self::EXAMPLE_CAMEL_CASE_WITH_NUMBERS, $textInCamel);
    }

    /** @test */
    public function snakeToCamel_trTRUtf8(): void
    {
        putenv('LC_ALL=tr_TR.utf8');
        setlocale(LC_ALL, 'tr_TR.utf8');

        $textInCamel = CaseHelper::snakeToCamel(self::EXAMPLE_SNAKE_CASE);
        $this->assertSame(self::EXAMPLE_CAMEL_CASE, $textInCamel);

        $textInCamel = CaseHelper::snakeToCamel(self::EXAMPLE_SNAKE_CASE_WITH_NUMBERS);
        $this->assertSame(self::EXAMPLE_CAMEL_CASE_WITH_NUMBERS, $textInCamel);

        putenv('LC_ALL=en_GB.utf8');
        setlocale(LC_ALL, 'en_GB.utf8');
    }

    /** @test */
    public function pascalToSnake(): void
    {
        $textInSnake = CaseHelper::pascalToSnake(self::EXAMPLE_PASCAL_CASE);
        $this->assertSame(self::EXAMPLE_SNAKE_CASE, $textInSnake);

        $textInSnake = CaseHelper::pascalToSnake(self::EXAMPLE_PASCAL_CASE_WITH_NUMBERS);
        $this->assertSame(self::EXAMPLE_SNAKE_CASE_WITH_NUMBERS, $textInSnake);
    }

    /** @test */
    public function pascalToSnake_trTRUtf8(): void
    {
        putenv('LC_ALL=tr_TR.utf8');
        setlocale(LC_ALL, 'tr_TR.utf8');

        $textInSnake = CaseHelper::pascalToSnake(self::EXAMPLE_PASCAL_CASE);
        $this->assertSame(self::EXAMPLE_SNAKE_CASE, $textInSnake);

        $textInSnake = CaseHelper::pascalToSnake(self::EXAMPLE_PASCAL_CASE_WITH_NUMBERS);
        $this->assertSame(self::EXAMPLE_SNAKE_CASE_WITH_NUMBERS, $textInSnake);

        putenv('LC_ALL=en_GB.utf8');
        setlocale(LC_ALL, 'en_GB.utf8');
    }


    /** @test */
    public function snakeToPascal(): void
    {
        $textInPascal = CaseHelper::snakeToPascal(self::EXAMPLE_SNAKE_CASE);
        $this->assertSame(self::EXAMPLE_PASCAL_CASE, $textInPascal);

        $textInPascal = CaseHelper::snakeToPascal(self::EXAMPLE_SNAKE_CASE_WITH_NUMBERS);
        $this->assertSame(self::EXAMPLE_PASCAL_CASE_WITH_NUMBERS, $textInPascal);
    }

    /** @test */
    public function snakeToPascal_trTRUtf8(): void
    {
        putenv('LC_ALL=tr_TR.utf8');
        setlocale(LC_ALL, 'tr_TR.utf8');

        $textInPascal = CaseHelper::snakeToPascal(self::EXAMPLE_SNAKE_CASE);
        $this->assertSame(self::EXAMPLE_PASCAL_CASE, $textInPascal);

        $textInPascal = CaseHelper::snakeToPascal(self::EXAMPLE_SNAKE_CASE_WITH_NUMBERS);
        $this->assertSame(self::EXAMPLE_PASCAL_CASE_WITH_NUMBERS, $textInPascal);

        putenv('LC_ALL=en_GB.utf8');
        setlocale(LC_ALL, 'en_GB.utf8');
    }

    /** @test */
    public function kebabToPascal(): void
    {
        $textInPascal = CaseHelper::kebabToPascal(self::EXAMPLE_KEBAB_CASE);
        $this->assertSame(self::EXAMPLE_PASCAL_CASE, $textInPascal);

        $textInPascal = CaseHelper::kebabToPascal(self::EXAMPLE_KEBAB_CASE_WITH_NUMBERS);
        $this->assertSame(self::EXAMPLE_PASCAL_CASE_WITH_NUMBERS, $textInPascal);
    }

    /** @test */
    public function kebabToPascal_trTRUtf8(): void
    {
        putenv('LC_ALL=tr_TR.utf8');
        setlocale(LC_ALL, 'tr_TR.utf8');

        $textInPascal = CaseHelper::kebabToPascal(self::EXAMPLE_KEBAB_CASE);
        $this->assertSame(self::EXAMPLE_PASCAL_CASE, $textInPascal);

        $textInPascal = CaseHelper::kebabToPascal(self::EXAMPLE_KEBAB_CASE_WITH_NUMBERS);
        $this->assertSame(self::EXAMPLE_PASCAL_CASE_WITH_NUMBERS, $textInPascal);

        putenv('LC_ALL=en_GB.utf8');
        setlocale(LC_ALL, 'en_GB.utf8');
    }

    /** @test */
    public function kebabToSnake(): void
    {
        $textInSnake = CaseHelper::kebabToSnake(self::EXAMPLE_KEBAB_CASE);
        $this->assertSame(self::EXAMPLE_SNAKE_CASE, $textInSnake);

        $textInSnake = CaseHelper::kebabToSnake(self::EXAMPLE_KEBAB_CASE_WITH_NUMBERS);
        $this->assertSame(self::EXAMPLE_SNAKE_CASE_WITH_NUMBERS, $textInSnake);
    }

    /** @test */
    public function kebabToSnake_trTRUtf8(): void
    {
        putenv('LC_ALL=tr_TR.utf8');
        setlocale(LC_ALL, 'tr_TR.utf8');

        $textInSnake = CaseHelper::kebabToSnake(self::EXAMPLE_KEBAB_CASE);
        $this->assertSame(self::EXAMPLE_SNAKE_CASE, $textInSnake);

        $textInSnake = CaseHelper::kebabToSnake(self::EXAMPLE_KEBAB_CASE_WITH_NUMBERS);
        $this->assertSame(self::EXAMPLE_SNAKE_CASE_WITH_NUMBERS, $textInSnake);

        putenv('LC_ALL=en_GB.utf8');
        setlocale(LC_ALL, 'en_GB.utf8');
    }

    /**
     * @test
     * @dataProvider providerSnakeToTitleCases
     */
    public function snakeToTitle(string $inputString, string $expectedResult): void
    {
        $convertedString = CaseHelper::snakeToTitle($inputString);
        $this->assertEquals($expectedResult, $convertedString);
    }

    /**
     * @test
     * @dataProvider providerSnakeToTitleCases
     */
    public function snakeToTitle_trTRUtf8(string $inputString, string $expectedResult): void
    {
        putenv('LC_ALL=tr_TR.utf8');
        setlocale(LC_ALL, 'tr_TR.utf8');

        $convertedString = CaseHelper::snakeToTitle($inputString);
        $this->assertEquals($expectedResult, $convertedString);

        putenv('LC_ALL=en_GB.utf8');
        setlocale(LC_ALL, 'en_GB.utf8');
    }

    public static function providerSnakeToTitleCases(): array
    {
        return [
            ['amount_balance', self::EXAMPLE_TITLE_CASE],
            ['amount', 'Amount'],
            ['', ''],
        ];
    }
}
