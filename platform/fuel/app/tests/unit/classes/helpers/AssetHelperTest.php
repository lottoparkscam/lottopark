<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers\AssetHelper;
use Test_Unit;

final class AssetHelperTest extends Test_Unit
{
    public function setUp(): void
    {
        parent::setUp();
        if (!function_exists('get_template_directory_uri')) {
            eval("
            function get_template_directory_uri(){
                return '';
            }
            ");
        }
    }

    /**
     * @test
     * @dataProvider getBaseImageDataProvider
     */
    public function getBaseImage(string $fileName, string $expected): void
    {
        $this->assertSame($expected, AssetHelper::getBaseImage($fileName));
    }

    public function getBaseImageDataProvider(): array
    {
        return [
            ['asd.png', '/images/asd.png'],
            ['asd.jpg', '/images/asd.jpg'],
            ['asd.jpeg', '/images/asd.jpeg'],
            ['asd', ''], // no extension
            ['asd.js', ''],
            ['asd.php', ''],
            ['asd.xsa', ''],
        ];
    }
}
