<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers\StringHelper;
use Test_Unit;

final class StringHelperTest extends Test_Unit
{
    /** @test */
    public function getStringBetween_shouldReturnContent(): void
    {
        $string = 'some random string';
        $expected = 'random';

        $this->assertSame($expected, StringHelper::getStringBetween($string, 'some ', ' string'));
    }

    /** @test */
    public function getStringBetween_stringDoesNotHaveStart_shouldReturnEmptyString(): void
    {
        $string = 'some random string';
        $expected = '';

        $this->assertSame($expected, StringHelper::getStringBetween($string, 'test ', ' string'));
    }

    /** @test */
    public function getStringBetween_stringDoesNotHaveEnd_shouldReturnEmptyString(): void
    {
        $string = 'some random string';
        $expected = '';

        $this->assertSame($expected, StringHelper::getStringBetween($string, 'some ', ' test'));
    }

    /** @test */
    public function getStringBetween_stringDoesNotHaveStartAndEndWord_shouldReturnEmptyString(): void
    {
        $string = 'some random string';
        $expected = '';

        $this->assertSame($expected, StringHelper::getStringBetween($string, 'asd ', ' bcd'));
    }

    /** @test */
    public function getStringAfterSubString_stringDoesNotHaveSubstring_shouldReturnEmptyString(): void
    {
        $string = 'some random';
        $expected = '';

        $this->assertSame($expected, StringHelper::getStringAfterSubString($string, 'xsda'));
    }

    /** @test */
    public function getStringAfterSubString_shouldReturnContentAfterWithSubString(): void
    {
        $string = 'some random text';
        $expected = 'random text';

        $this->assertSame($expected, StringHelper::getStringAfterSubString($string, 'random'));
    }

    /** @test */
    public function getStringAfterSubString_shouldReturnContentAfterWithoutSubString(): void
    {
        $string = 'some random text';
        $expected = ' text';

        $this->assertSame($expected, StringHelper::getStringAfterSubString($string, 'random', false));
    }
}
