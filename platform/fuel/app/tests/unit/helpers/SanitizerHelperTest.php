<?php

namespace Tests;

use Helpers\SanitizerHelper;
use Test_Unit;

class SanitizerHelperTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider sanitizeSlugProvider
     */
    public function sanitizeSlug(string $given, string $expected): void
    {
        // When
        $actual = SanitizerHelper::sanitizeSlug($given);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function sanitizeSlugProvider(): array
    {
        return [
            ['lottery-slug', 'lottery-slug'],
            ['lottery-slug/!@#$%^&*()<>ßį§', 'lottery-slug'],
            ['1lottery2-3slug/!@#$%^&*()<>ßį§', '1lottery2-3slug'],
        ];
    }
}
