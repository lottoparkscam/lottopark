<?php

namespace Tests\Unit\Classes\Helpers;

use Lotto_Helper;
use Models\Whitelabel;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class LottoHelperTest extends Test_Unit
{
    private ConfigContract $configContract;

    public function setUp(): void
    {
        parent::setUp();
        $this->configContract = $this->container->get(ConfigContract::class);
    }

    /**
     * @test
     * @dataProvider provider
     */
    public function getWhitelabelDomainFromUrl_shouldReturnCorrectDomain(string $httpHost, string $expectedDomain)
    {
        $_SERVER['HTTP_HOST'] = $httpHost;
        $domain = Lotto_Helper::getWhitelabelDomainFromUrl();
        $this->assertSame($expectedDomain, $domain);
    }

    /**
     * HTTP HOST cases across environments.
     * @return string
     */
    public function provider(): array
    {
        return [
            ['www.lovcasino.com', 'lovcasino.com'],
            ['api.lovcasino.com', 'lovcasino.com'],
            ['aff.lovcasino.com', 'lovcasino.com'],
            ['manager.lovcasino.com', 'lovcasino.com'],
            ['empire.lovcasino.com', 'lovcasino.com'],
            ['manager.pre-master.lovcasino.com', 'pre-master.lovcasino.com'],
            ['lovcasino.com', 'lovcasino.com'],
            ['www.lottopark.com', 'lottopark.com'],
            ['api.lottopark.com', 'lottopark.com'],
            ['aff.lottopark.com', 'lottopark.com'],
            ['manager.lottopark.com', 'lottopark.com'],
            ['empire.lottopark.com', 'lottopark.com'],
            ['manager.pre-master.lottopark.com', 'pre-master.lottopark.com'],
            ['lottopark.com', 'lottopark.com'],
            ['managermanager.lottopark.com', 'managermanager.lottopark.com'],
            ['manager.manager.lottopark.com', 'manager.lottopark.com'],
            ['manager.16-branchname.lottopark.com', '16-branchname.lottopark.com'],
            ['www.casino.lottohoy.com', 'lottohoy.com'], // assertion that limit 1 is local not global - both www. and casino. will be stripped. this concrete case is not real but it's important to check underlying pattern.
        ];
    }

    /**
     * use snake in order to not destroy search ability
     * @test
     */
    public function get_wordpress_file_url_path__ThemeIsUpperCase_ShouldReturnValid(): void
    {
        $wpPath = $this->configContract->get('wordpress.path');

        $expected = [
            'path' => $wpPath . "/wp-content/themes/lottopark/images/logo.png",
            'url' => 'https://lottopark.loc/wp-content/themes/lottopark/images/logo.png'
        ];
        $whitelabel = $this->container->get('whitelabel');
        $whitelabel->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabel->domain = 'lottopark.loc';
        $whitelabel = $whitelabel->to_array();
        $actual = Lotto_Helper::get_wordpress_file_url_path($whitelabel, 'images/logo.png');

        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function get_wordpress_file_url_path__FileInCamel_ShouldNotChangeIt(): void
    {
        $wpPath = $this->configContract->get('wordpress.path');

        $file = 'images/TeSxTFx.png';
        $expected = [
            'path' => $wpPath . "/wp-content/themes/lottopark/" . $file,
            'url' => 'https://lottopark.loc/wp-content/themes/lottopark/' . $file
        ];
        $whitelabel = $this->container->get('whitelabel');
        $whitelabel->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabel->domain = 'lottopark.loc';
        $whitelabel = $whitelabel->to_array();
        $actual = Lotto_Helper::get_wordpress_file_url_path($whitelabel, $file);

        $this->assertSame($expected, $actual);
    }
}
