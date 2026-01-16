<?php

namespace Tests\Unit\Classes\Services\Files;

use Services\Files\FileService;
use Test_Unit;

final class FileServiceTest extends Test_Unit
{
    private FileService $fileService;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileService = $this->container->get(FileService::class);
    }

    /** @test */
    public function convertSizeFromBytesToKB(): void
    {
        $actual = $this->fileService->convertSizeFromBytes(1024, 'KB', 1);
        $expected = 1.0;

        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function convertSizeFromBytesToMB(): void
    {
        $twoMBInBytes = 2097152;
        $actual = $this->fileService->convertSizeFromBytes($twoMBInBytes, 'MB', 1);
        $expected = 2.0;

        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function convertSizeFromBytesToGB(): void
    {
        $fiveGBInBytes = 5368709120;
        $actual = $this->fileService->convertSizeFromBytes($fiveGBInBytes, 'GB', 1);
        $expected = 5.0;

        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function convertSizeFromBytesToGBIsLowerThanGbWillReturn0(): void
    {
        $fiveGBInBytes = 1;
        $actual = $this->fileService->convertSizeFromBytes($fiveGBInBytes, 'GB', 1);
        $expected = 0.0;

        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function convertSizeFromBytesToGBPrecisionWorksProperly(): void
    {
        $fiveGBInBytes = 204800;
        $actual = $this->fileService->convertSizeFromBytes($fiveGBInBytes, 'GB', 4);
        $expected = 0.0002;

        $this->assertSame($expected, $actual);
    }
}
