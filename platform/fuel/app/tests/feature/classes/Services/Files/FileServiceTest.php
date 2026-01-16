<?php

namespace Tests\Feature\Classes\Services\Files;

use Exceptions\Files\FileNotFoundException;
use Services\Files\FileService;
use Test_Feature;

final class FileServiceTest extends Test_Feature
{
    private FileService $fileService;
    private const TEST_FILE_PATH = '/var/log/php/whitelotto/tests/fileService.example';
    private const TEST_FILE_FOLDER = '/var/log/php/whitelotto/tests';

    private const TEST_JSON = '{"type":"ERROR","source":"PLATFORM WORDPRESS & DEFAULT", 
    "message":"Error - syntax error, unexpected variable \"$casinoUrl\" in \/var\/www\/wordpress\/wp-content\/themes\/base\/template-casino.php on line 8"
    ,"date":"2022-06-21 10:06:09 UTC","url":"https:\/\/casino.lottopark.loc\/",
    "file":"\/var\/www\/platform\/fuel\/core\/base.php:73",
    "trace":"#1 function: Log::write(400, Error - syntax error, unexpected variable \"$casinoUrl\" in \/var\/www\/wordpress\/wp-content\/themes\/base\/template-casino.php on line 8, 
    {\"exception\":{}}), \/var\/www\/platform\/fuel\/core\/base.php:73 \r
    #2 function: logger(400, Error - syntax error, unexpected variable \"$casinoUrl\" in \/var\/www\/wordpress\/wp-content\/themes\/base\/template-casino.php on line 8,
    {\"exception\":{}}), \/var\/www\/platform\/fuel\/core\/classes\/errorhandler.php:128 \r
    #3 function: Fuel\\Core\\Errorhandler::exception_handler(ParseError), \/var\/www\/wordpress\/wp-content\/plugins\/lotto-platform\/includes\/fuel.php:97 \r#4 function: {closure}(ParseError), : \r","count":1}';

    public function setUp(): void
    {
        parent::setUp();
        $this->fileService = $this->container->get(FileService::class);

        if (!is_dir(self::TEST_FILE_FOLDER)) {
            mkdir(self::TEST_FILE_FOLDER, 755, true);
        }

        file_put_contents(self::TEST_FILE_PATH, '');
    }

    public function tearDown(): void
    {
        unlink(self::TEST_FILE_PATH);
    }

    /** @test */
    public function existsFileExistsShouldReturnTrue(): void
    {
        $fileExists = $this->fileService->exists(self::TEST_FILE_PATH);
        $this->assertTrue($fileExists);
    }

    /** @test */
    public function existsFileNotExistsShouldThrow(): void
    {
        $path = self::TEST_FILE_FOLDER . '/newfolder/asd.log';
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('Cannot find file with provided path: ' . $path);
        $this->fileService->exists($path);
    }

    /** @test */
    public function createIfNotExistsShouldCreate(): void
    {
        unlink(self::TEST_FILE_PATH);
        $isSuccess = $this->fileService->createIfNotExists(self::TEST_FILE_PATH);
        $this->assertTrue($isSuccess);
    }

    /** @test */
    public function createIfNotExistsShouldCreateRecursive(): void
    {
        $pathToFile = self::TEST_FILE_FOLDER . 'newfolder/test.log';
        $isSuccess = $this->fileService->createIfNotExists($pathToFile, true);
        $this->assertTrue($isSuccess);
        $this->assertFileExists($pathToFile);
        unlink($pathToFile);
    }

    /** @test */
    public function createIfNotExistsShouldNotCreateOnSecondCall(): void
    {
        unlink(self::TEST_FILE_PATH);
        $isSuccess = $this->fileService->createIfNotExists(self::TEST_FILE_PATH);
        $this->assertTrue($isSuccess);
        $expectedCreateTime = filemtime(self::TEST_FILE_PATH);

        $isSuccess = $this->fileService->createIfNotExists(self::TEST_FILE_PATH);
        $this->assertTrue($isSuccess);
        $actualCreateTime = filemtime(self::TEST_FILE_PATH);
        $this->assertSame($expectedCreateTime, $actualCreateTime);
    }

    /** @test */
    public function pushToLineShouldPushToProvidedLine(): void
    {
        file_put_contents(
            self::TEST_FILE_PATH,
            "LINE 0 \nLINE 1 \nLINE 2 \n",
        );

        $this->assertFileExists(self::TEST_FILE_PATH);

        $isSuccess = $this->fileService->pushToLine(self::TEST_FILE_PATH, 'MY CUSTOM LINE', 1);
        $this->assertTrue($isSuccess);

        $actual = file_get_contents(self::TEST_FILE_PATH);
        $expected = "LINE 0 \nMY CUSTOM LINE\nLINE 1 \nLINE 2 \n";
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function pushToLineProvidedLineDoesNotExistShouldReturnFalse(): void
    {
        file_put_contents(
            self::TEST_FILE_PATH,
            "LINE 0 \nLINE 1 \nLINE 2 \n",
        );

        $this->assertFileExists(self::TEST_FILE_PATH);

        $isSuccess = $this->fileService->pushToLine(self::TEST_FILE_PATH, 'MY CUSTOM LINE', 99);
        $this->assertFalse($isSuccess);

        // file content shouldn't have changed
        $actual = file_get_contents(self::TEST_FILE_PATH);
        $expected = "LINE 0 \nLINE 1 \nLINE 2 \n";
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getAfterLineShouldReturnAllLinesAfterSpecifiedLine(): void
    {
        file_put_contents(
            self::TEST_FILE_PATH,
            "LINE 0 \nLINE 1 \nLINE 2",
        );

        $this->assertFileExists(self::TEST_FILE_PATH);

        $expected = [
            1 => "LINE 1 \n",
            2 => "LINE 2"
        ];
        $actual = $this->fileService->getAfterLine(self::TEST_FILE_PATH, 1);

        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getAfterLineProvidedLineDoesNotExistShouldReturnEmptyArray(): void
    {
        file_put_contents(
            self::TEST_FILE_PATH,
            "LINE 0 \nLINE 1 \nLINE 2 \n",
        );

        $this->assertFileExists(self::TEST_FILE_PATH);

        $expected = [];
        $actual = $this->fileService->getAfterLine(self::TEST_FILE_PATH, 4);

        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getAfterLineWithProvidedLimit(): void
    {
        file_put_contents(
            self::TEST_FILE_PATH,
            "LINE 0 \nLINE 1 \nLINE 2 \nLINE 3 \n",
        );

        $this->assertFileExists(self::TEST_FILE_PATH);

        $expected = [
            1 => "LINE 1 \n",
            2 => "LINE 2 \n"
        ];
        $actual = $this->fileService->getAfterLine(self::TEST_FILE_PATH, 1, 2);

        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getFirstLineShouldReturnValid(): void
    {
        file_put_contents(
            self::TEST_FILE_PATH,
            "LINE 0\nLINE 1 \nLINE 2 \nLINE 3 \n",
        );

        $this->assertFileExists(self::TEST_FILE_PATH);

        $expected = "LINE 0";
        $actual = $this->fileService->getFirstLine(self::TEST_FILE_PATH);

        $this->assertSame($expected, $actual);
    }


    /**
     * @test
     * @dataProvider fileContainsDataProvider
     */
    public function fileContains(string $fileContent, array $searchWords, bool $expected): void
    {
        $testFile = 'fileContains.log';
        file_put_contents($testFile, $fileContent);
        $result = $this->fileService->fileContains($testFile, $searchWords);
        $this->assertSame($expected, $result);
        unlink($testFile);
    }

    public function fileContainsDataProvider(): array
    {
        return [
            // no json
            ['asd', ['asd'], true],
            ['asd', ['opiasdasx'], false],
            // json
            [
                self::TEST_JSON,
                [
                    'Error - syntax error, unexpected variable "$casinoUrl" in /var/www/wordpress/wp-content/themes/base/template-casino.php on line 8',
                    'https://casino.lottopark.loc/',
                    '/var/www/platform/fuel/core/base.php:73'
                ],
                true
            ],
            [
                self::TEST_JSON,
                [
                    'Error - syntax error, unexpected variable "$casinoUrl" in /var/www/wordpress/wp-content/themes/base/template-casino.php on line 8',
                    'https://casino.lottopark.loc/',
                    '/var/www/platform/fuel/core/base.php:88'
                ],
                false
            ],
            [
                self::TEST_JSON,
                [
                    'Error - syntax error, unexpected variable "$casinoUrl" in /var/www/wordpress/wp-content/themes/base/template-casino.php on line 8',
                    'https://lottopark.loc',
                    '/var/www/platform/fuel/core/base.php:73'
                ],
                false
            ],
            [
                self::TEST_JSON,
                [
                    'Error - syntax error, unexpected variable "$casinoUrls" in /var/www/wordpress/wp-content/themes/base/template-casino.php on line 8',
                    'https://casino.lottopark.loc/',
                    '/var/www/platform/fuel/core/base.php:73'
                ],
                false
            ],
        ];
    }
}
