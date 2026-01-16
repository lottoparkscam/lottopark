<?php

namespace Tests\Unit\Helpers;

use Test_Unit;
use ReflectionClass;
use Helper_Migration;

class MigrationTest extends Test_Unit
{
    /** We use those private functions to get access for this function; it's private */
    private static function getMethod(string $name): object
    {
        $migrationHelper = new ReflectionClass('Helper_Migration');
        $method = $migrationHelper->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    private function shortenConstraintIfLengthIsWrongTest(string $constraint): string
    {
        $method = self::getMethod('shortenConstraintIfLengthIsWrong');
        $migrationHelper = new Helper_Migration();
        return $method->invokeArgs($migrationHelper, [$constraint]);
    }

    /** @test */
    public function shortenConstraintIfLengthIsWrong_constraintIsValid_ReturnTheSame(): void
    {
        $constraint = 'whitelabel_default_currency_foreign';
        $actual = $this->shortenConstraintIfLengthIsWrongTest($constraint);
        $this->assertSame($actual, $constraint);
    }

    /** @test */
    public function shortenConstraintIfLengthIsWrong_constraintIsTooLong_ReturnShorted(): void
    {
        $constraint = 'whitelabel_disabled_currency_enabled_whitelabel_calculation_currency_margin';
        $actual = $this->shortenConstraintIfLengthIsWrongTest($constraint);

        $expected = 'wl_off_curr_on_wl_calc_curr_marg';

        $this->assertSame($actual, $expected);
    }
}
