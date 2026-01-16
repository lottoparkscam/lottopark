<?php

namespace Feature\Modules\View;

use Modules\View\ViewRendererContract;
use Path;
use RuntimeException;
use Test_Feature;

use const APPPATH;
use const PATHINFO_EXTENSION;

class ViewRendererTest extends Test_Feature
{
    private static string $basePath;
    private const TWIG_CONTENT = '<h1>Test</h1><p>Welcome {{ test }}</p>';
    private const PHP_CONTENT = '<h1>Test</h1><p>Welcome <?= $test ?></p>';

    private ViewRendererContract $viewRenderer;

    public function setUp(): void
    {
        parent::setUp();
        self::$basePath = APPPATH . Path::unifyPath('../../../wordpress/wp-content/themes/base/');
        $this->viewRenderer = $this->container->get(ViewRendererContract::class);
    }

    /** @test */
    public function render__twig_file(): void
    {
        // Given
        $value = 'John';
        $expected = str_replace('{{ test }}', $value, self::TWIG_CONTENT);

        // When
        $actual = $this->viewRenderer->render(self::$basePath . 'example.twig', ['test' => $value]);

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @dataProvider fileNameProvider
     * @param string $fileName
     */
    public function render__file_by_short_name(string $fileName): void
    {
        // When
        $actual = $this->viewRenderer->render($fileName, ['test' => 123]);

        // Then
        $this->assertNotEmpty($actual);
    }

    public function fileNameProvider(): array
    {
        return [
            '.twig' => ['example.twig'],
            '.php' => ['example.php'],
            'none' => ['example'],
        ];
    }

    /** @test */
    public function render__php_file(): void
    {
        // Given
        $value = 'John';
        $expected = str_replace('<?= $test ?>', $value, self::PHP_CONTENT);

        // When
        $actual = $this->viewRenderer->render(self::$basePath . 'example.php', ['test' => $value]);

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @dataProvider fileNameProvider
     * @param string $fileName
     */
    public function render__php_file_exists_in_many_locations__throws_exception(string $fileName): void
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION) ?: 'php';
        $path = Path::unifyPath(APPPATH . 'classes\modules\Payments\Jeton\resources\\');
        file_put_contents($path . 'example.' . $extension, self::PHP_CONTENT);

        // Except
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches("~in more than one location~");

        // When
        $actual = $this->viewRenderer->render($fileName, ['test' => 123]);

        // Then
        $this->assertNotEmpty($actual);
    }

    public static function setUpBeforeClass(): void
    {
        $unifiedPath = APPPATH . Path::unifyPath('../../../wordpress/wp-content/themes/base/');
        file_put_contents($unifiedPath . 'example.twig', self::TWIG_CONTENT);
        file_put_contents($unifiedPath . 'example.php', self::PHP_CONTENT);

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        $unifiedPath = APPPATH . Path::unifyPath('../../../wordpress/wp-content/themes/base/');
        @unlink($unifiedPath . 'example.twig');
        @unlink($unifiedPath . 'example.php');
        @unlink(APPPATH . Path::unifyPath('classes\modules\Payments\Jeton\resources\example.php'));
        @unlink(APPPATH . Path::unifyPath('classes\modules\Payments\Jeton\resources\example.twig'));

        parent::tearDownAfterClass();
    }
}
