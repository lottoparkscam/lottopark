<?php

namespace Tests\Unit\Helpers\Wordpress;

use Helpers\Wordpress\LanguageHelper;
use Helpers_App;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\MockObject\MockObject;
use Test_Unit;

class LanguageHelperTest extends Test_Unit
{
    use PHPMock;

    private MockObject $applyFiltersMock;

    public function setUp(): void
    {
        $this->applyFiltersMock = $this->getFunctionMock('Helpers\Wordpress', 'apply_filters');
        $wpmlActiveLanguagesJsonPath = Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/Helpers/Wordpress/WpmlActiveLanguages.json');
        $wpmlActiveLanguages = json_decode(file_get_contents($wpmlActiveLanguagesJsonPath), true);
        $this->applyFiltersMock->expects($this->any())->willReturn($wpmlActiveLanguages);
        parent::setUp();
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function getCurrentLanguageShortcode_inWpCli(): void
    {
        define('WP_CLI', true);
        $actual = LanguageHelper::getCurrentLanguageShortcode();
        $expected = 'en';
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function getCurrentWhitelabelLanguage_inWpCli(): void
    {
        define('WP_CLI', true);
        $actual = LanguageHelper::getCurrentWhitelabelLanguage();
        $this->assertNull($actual);
    }
}
