<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers\ImageHelper;
use Test_Unit;

class ImageHelperTest extends Test_Unit
{
    /** @test */
    public function constBase64ImageEqualsCorrectImage(): void
    {
        $this->assertEquals(ImageHelper::BASE_64_IMAGE, 'data:image/png;base64,');
    }

    /** @test */
    public function generateBase64Image(): void
    {
        $result = ImageHelper::generateBase64Image('asdadasdasda');
        $this->assertTrue(ImageHelper::isImageBase64Encoded($result));
    }

    /**
     * @test
     * @dataProvider providerTestImageCases
     * @param string $inputImages
     * @param bool $expectedResult
     */
    public function isImageBase64Encoded(string $inputImages, bool $expectedResult): void
    {
        $result = ImageHelper::isImageBase64Encoded($inputImages);
        $this->assertEquals($expectedResult, $result);
    }

    public static function providerTestImageCases(): array
    {
        return [
            ['data:image/png;base65,', false],
            ['data:image/png;base64', false],
            ['', false],
            [ImageHelper::BASE_64_IMAGE, true],
            ["data:image/png;base64, asd", true]
        ];
    }
}
