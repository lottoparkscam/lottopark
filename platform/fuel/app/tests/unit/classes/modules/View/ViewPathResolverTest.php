<?php

namespace Unit\Modules\View;

use InvalidArgumentException;
use Modules\View\ViewPathResolver;
use RuntimeException;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;
use Wrappers\File;

class ViewPathResolverTest extends Test_Unit
{
    private ConfigContract $config;
    private File $file;

    private ViewPathResolver $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(ConfigContract::class);
        $this->file = $this->createMock(File::class);
        $this->service = new ViewPathResolver($this->config, $this->file);
    }

    /** @test */
    public function resolveFilePath__has_extension_and_is_not_supported__throws_invalid_argument_exception(): void
    {
        // Except
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected one of: "txt". Got: "php"');

        // Given
        $file = __FILE__;

        // When
        $this->service->resolveFilePath($file, ['txt']);
    }

    /** @test */
    public function resolveFilePath__is_path_absolute_and_has_extension__returns_path(): void
    {
        // Given
        $file = __FILE__;
        $expected = $file;

        // When
        $actual = $this->service->resolveFilePath($file, ['php']);

        // Then
        $this->assertSame($actual, $expected);
    }

    /** @test */
    public function resolveFilePath__file_exists_in_many_locations__throws_runtime_exception(): void
    {
        // Given
        $file = 'file.php';
        $conflictStrategy = 'php';

        // Except
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Found $file in more than one location");

        // Given
        $supportedExceptions = ['php'];

        $dirs = [
            __DIR__,
            __DIR__ . '/subid'
        ];

        $this->config
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['view.template_directories'], ['view.conflict_strategy'])
            ->willReturnOnConsecutiveCalls($dirs, $conflictStrategy);

        $this->file
            ->expects($this->exactly(2))
            ->method('exists')
            ->willReturn(true);

        // When
        $this->service->resolveFilePath($file, $supportedExceptions);
    }

    /** @test */
    public function resolveFilePath__no_files_found__throws_runtime_exception(): void
    {
        // Given
        $file = 'file.php';
        $conflictStrategy = 'php';

        // Except
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Attempting to find view file.php, no file exists in configured locations.');

        // Given
        $supportedExceptions = ['php'];

        $dirs = [
            __DIR__,
            __DIR__ . '/subid'
        ];

        $this->config
            ->method('get')
            ->withConsecutive(['view.template_directories'], ['view.conflict_strategy'])
            ->willReturnOnConsecutiveCalls($dirs, $conflictStrategy);

        $this->file
            ->method('exists')
            ->willReturn(false);

        // When
        $this->service->resolveFilePath($file, $supportedExceptions);
    }

    /** @test */
    public function resolveFilePath__file_has_extension__returns_path(): void
    {
        // Given
        $file = 'file.php';
        $conflictStrategy = 'php';
        $supportedExceptions = ['php'];

        $dirs = [
            __DIR__,
        ];

        $expected = $dirs[0] . $file;

        $this->config
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['view.template_directories'], ['view.conflict_strategy'])
            ->willReturnOnConsecutiveCalls($dirs, $conflictStrategy);

        $this->file
            ->expects($this->exactly(1))
            ->method('exists')
            ->willReturnOnConsecutiveCalls(true);

        // When
        $actual = $this->service->resolveFilePath($file, $supportedExceptions);

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function resolveFilePath__file_has_not_extension__returns_path(): void
    {
        // Given
        $file = 'file';
        $conflictStrategy = 'php';
        $supportedExceptions = ['php'];

        $dirs = [
            __DIR__,
        ];

        $expected = "{$dirs[0]}{$file}.{$conflictStrategy}";

        $this->config
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['view.template_directories'], ['view.conflict_strategy'])
            ->willReturnOnConsecutiveCalls($dirs, $conflictStrategy);

        $this->file
            ->expects($this->exactly(1))
            ->method('exists')
            ->willReturnOnConsecutiveCalls(true);

        // When
        $actual = $this->service->resolveFilePath($file, $supportedExceptions);

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function resolveFilePath__file_has_not_extension_and_many_files_found__returns_path_by_conflict_strategy(): void
    {
        // Given
        $file = 'file';
        $conflictStrategy = null;
        $supportedExceptions = ['php', 'twig'];

        // Except
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Attempting to find view file, but many files with the same name exists in configured locations and no conflict strategy set in config file');

        $dirs = [
            __DIR__,
        ];

        $this->config
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['view.template_directories'], ['view.conflict_strategy'])
            ->willReturnOnConsecutiveCalls($dirs, $conflictStrategy);

        $this->file
            ->expects($this->exactly(count($supportedExceptions)))
            ->method('exists')
            ->willReturn(true);

        // When
        $this->service->resolveFilePath($file, $supportedExceptions);
    }

    /** @test */
    public function resolveFilePath__file_has_not_extension_and_no_conflict_strategy_set__throws_invalid_argument_exception(): void
    {
        // Given
        $file = 'file';
        $conflictStrategy = 'php';
        $supportedExceptions = ['php', 'twig'];

        $dirs = [
            __DIR__,
        ];

        $expected = "{$dirs[0]}{$file}.{$conflictStrategy}";

        $this->config
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['view.template_directories'], ['view.conflict_strategy'])
            ->willReturnOnConsecutiveCalls($dirs, $conflictStrategy);

        $this->file
            ->expects($this->exactly(count($supportedExceptions)))
            ->method('exists')
            ->willReturn(true);

        // When
        $actual = $this->service->resolveFilePath($file, $supportedExceptions);

        // Then
        $this->assertSame($expected, $actual);
    }
}
