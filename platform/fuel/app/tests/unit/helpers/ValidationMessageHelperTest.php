<?php

namespace Tests\Unit\Helpers;

use Helpers\ValidationMessageHelper;
use Test_Unit;

final class ValidationMessageHelperTest extends Test_Unit
{
    private const FIRST_ERROR = '1st error';
    private const SECOND_ERROR = '2nd error';

    /** @test */
    public function displayOnFront_HasManyErrors_ShouldDisplayAll(): void
    {
        $output = '';
        ob_start();
        ValidationMessageHelper::displayOnFront([
            'contactPhone' => self::FIRST_ERROR,
            'contactEmail' => self::SECOND_ERROR,
        ]);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString(self::FIRST_ERROR, $output);
        $this->assertStringContainsString(self::SECOND_ERROR, $output);
    }

    /** @test */
    public function displayOnFront_HasOneError_ShouldDisplay(): void
    {
        $output = '';
        ob_start();
        ValidationMessageHelper::displayOnFront([
            'contactPhone' => self::FIRST_ERROR,
        ]);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString(self::FIRST_ERROR, $output);
    }

    /** @test */
    public function displayOnFront_HasNotError_ShouldDisplayNoting(): void
    {
        $output = '';
        ob_start();
        ValidationMessageHelper::displayOnFront([]);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEmpty($output);
    }
}
