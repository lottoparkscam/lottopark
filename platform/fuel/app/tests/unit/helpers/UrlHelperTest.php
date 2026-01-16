<?php

namespace Tests\Unit\Helpers;

use Fuel\Core\PhpErrorException;
use Helpers\UrlHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Test_Unit;
use Lotto_Helper;
use phpmock\phpunit\PHPMock;

final class UrlHelperTest extends Test_Unit
{
    use PHPMock;

    private const URL = 'lottopark.loc';
    private const LOTTO_PLATFORM_GET_POST_ID_BY_SLUG = 'lotto_platform_get_post_id_by_slug';
    private MockObject $lottoPlatformHomeUrlMock;

    public function setUp(): void
    {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = self::URL;
        $_SERVER['SITE_URL'] = 'https://' . self::URL;
        $this->lottoPlatformHomeUrlMock = $this->getFunctionMock('Helpers', 'lotto_platform_home_url');
    }

    /** @test */
    public function isHomepage_IsHomepageWithLanguage(): void
    {
        $_SERVER['REQUEST_URI'] = '/pl';
        $isHomepage = UrlHelper::isHomepage('pl');
        $this->assertTrue($isHomepage);
    }

    /** @test */
    public function isHomepage_IsHomepageWithoutLanguage(): void
    {
        $_SERVER['REQUEST_URI'] = '/';
        $isHomepage = UrlHelper::isHomepage('en');
        $this->assertTrue($isHomepage);
    }

    /** @test */
    public function isHomepage_IsNotHomepageWithLanguage(): void
    {
        $_SERVER['REQUEST_URI'] = '/pl/faq';
        $isHomepage = UrlHelper::isHomepage('pl');
        $this->assertFalse($isHomepage);
    }

    /** @test */
    public function isHomepage_IsNotHomepageWithoutLanguage(): void
    {
        $_SERVER['REQUEST_URI'] = '/faq';
        $isHomepage = UrlHelper::isHomepage('en');
        $this->assertFalse($isHomepage);
    }

    /** @test */
    public function isHomepage_IsNotHomepageWithoutLanguageAndWithParams(): void
    {
        $_SERVER['REQUEST_URI'] = '/faq?author=2&provider=2';
        $isHomepage = UrlHelper::isHomepage('en');
        $this->assertFalse($isHomepage);
    }

    /** @test */
    public function isHomepage_IsNotHomepageWithLanguageAndParams(): void
    {
        $_SERVER['REQUEST_URI'] = '/pl/faq?author=2&provider=2';
        $isHomepage = UrlHelper::isHomepage('pl');
        $this->assertFalse($isHomepage);
    }

    /** @test */
    public function isHomepage_IsHomepageWithLanguageAndParams(): void
    {
        $_SERVER['REQUEST_URI'] = '/pl?author=2&provider=2';
        $isHomepage = UrlHelper::isHomepage('pl');
        $this->assertTrue($isHomepage);
    }

    /** @test */
    public function getCurrentUrlWithParams_shouldReturnSame()
    {
        $_SERVER['HTTP_HOST'] = rtrim('https://testurl.abcdef/', '/');
        $_SERVER['REQUEST_URI'] = '?test=1&test2=2';
        $link = 'https://testurl.abcdef?test=1&test2=2';

        $this->assertSame($link, UrlHelper::getCurrentUrlWithParams());
    }

    /** @test */
    public function getCurrentUrlWithParams_shouldReturnFalse()
    {
        $_SERVER['HTTP_HOST'] = rtrim('https://testurl.abcdef/', '/');
        $_SERVER['REQUEST_URI'] = '?test=1&test2=2';

        $link = 'https://testurl.abcdef?test=1&test2=234323431242';

        $isTheSame = $link === UrlHelper::getCurrentUrlWithParams();
        $this->assertFalse($isTheSame);
    }

    /** @test */
    public function getCurrentUrlWithParams_shouldReturnWithCasino()
    {
        $_SERVER['HTTP_HOST'] = rtrim('https://casino.lottopark.loc/', '/');
        $_SERVER['REQUEST_URI'] = '?test=1&test2=234323431242';

        $link = 'https://casino.lottopark.loc?test=1&test2=234323431242';

        $this->assertSame($link, UrlHelper::getCurrentUrlWithParams());
    }

    /**
     * @test
     * @dataProvider noSlashAtTheEndDataProvider
     */
    public function addEndingSlash_noSlashAtTheEnd_AddsSlash(string $url): void
    {
        UrlHelper::add_ending_slash($url);
        $this->assertSame('/', substr($url, -1));
    }

    public function noSlashAtTheEndDataProvider(): array
    {
        return [
            ['https://lottopark.com'],
            ['https://lottopark.loc/play/euromillions'],
            ['https://lottopark.loc/play/powerball'],
        ];
    }

    /** @test */
    public function addEndingSlash_SlashAtTheEnd_NotAddDoubleSlash(): void
    {
        $url = 'https://lottopark.com/';
        UrlHelper::add_ending_slash($url);
        $this->assertSame('https://lottopark.com/', $url);
    }

    /** @test */
    public function addEndingSlash_UrlHasGetParams_PutsSlashesBeforeQuestionMark(): void
    {
        $url = 'https://lottopark.com/some-page?param1=12&param2=3';
        UrlHelper::add_ending_slash($url);
        $this->assertSame('https://lottopark.com/some-page/?param1=12&param2=3', $url);
    }

    /**
     * @test
     * @param string $url
     * @dataProvider urlIsNotRegularRoutePathDataProvider
     */
    public function addEndingSlash_UrlIsNotRegularRoutePath_NotAddSlashAtTheEnd(string $url): void
    {
        UrlHelper::add_ending_slash($url);
        $this->assertNotSame('/', substr($url, -1));
    }

    public function urlIsNotRegularRoutePathDataProvider(): array
    {
        return [
            ['https://lottopark.loc/wp-content/plugins/lotto-platform/public/images/lotteries/lottery_30.png'],
            ['https://lottopark.loc/wp-includes/css/dist/block-library/style.min.css'],
            ['https://www.google.com/recaptcha/api.js?hl=en&#038;onload=recaptchaLottoCallback&#038;render=explicit'],
        ];
    }

    /** @test */
    public function changeUrlsToCasino_casinoPageExistsWithENLanguage_ShouldChangeToCasinoWithoutLanguage(): void
    {
        $_SERVER['REQUEST_URI'] = '/en';

        $casinoPage = new class {
            public $id = 1;
            public $post_name = 'casino';
        };

        $functionExistsMock = $this->getFunctionMock(
            'Helpers',
            'function_exists'
        );

        $getPostIdBySlugMock = $this->getFunctionMock(
            'Helpers',
            self::LOTTO_PLATFORM_GET_POST_ID_BY_SLUG
        );

        $getPostMock = $this->getFunctionMock(
            'Helpers',
            'get_post'
        );

        $functionExistsMock->expects($this->once())
            ->with(self::LOTTO_PLATFORM_GET_POST_ID_BY_SLUG)->willReturn(true);

        $getPostIdBySlugMock->expects($this->once())
            ->with('casino')->willReturn($casinoPage->id);

        $getPostMock->expects($this->once())
            ->with($casinoPage->id)->willReturn($casinoPage);

        $body = '<div class="some-html-content"><a href="https://lottopark.loc/casino"></a></div>';
        $expected = '<div class="some-html-content"><a href="https://casino.lottopark.loc/"></a></div>';

        $changedBody = UrlHelper::changeUrlsToCasino($body);

        $this->assertEquals($expected, $changedBody);
    }

    /** @test */
    public function changeUrlsToCasino_casinoPageExistsWithEmptyLanguageValue_ShouldChangeToCasinoWithoutLanguage(): void
    {
        $casinoPage = new class {
            public $id = 1;
            public $post_name = 'casino';
        };

        $functionExistsMock = $this->getFunctionMock(
            'Helpers',
            'function_exists'
        );

        $getPostIdBySlugMock = $this->getFunctionMock(
            'Helpers',
            self::LOTTO_PLATFORM_GET_POST_ID_BY_SLUG
        );

        $getPostMock = $this->getFunctionMock(
            'Helpers',
            'get_post'
        );

        $functionExistsMock->expects($this->once())
            ->with(self::LOTTO_PLATFORM_GET_POST_ID_BY_SLUG)->willReturn(true);

        $getPostIdBySlugMock->expects($this->once())
            ->with('casino')->willReturn($casinoPage->id);

        $getPostMock->expects($this->once())
            ->with($casinoPage->id)->willReturn($casinoPage);

        $body = '<div class="some-html-content"><a href="https://lottopark.loc/casino"></a></div>';
        $expected = '<div class="some-html-content"><a href="https://casino.lottopark.loc/"></a></div>';

        $changedBody = UrlHelper::changeUrlsToCasino($body);

        $this->assertEquals($expected, $changedBody);
    }

    /** @test */
    public function changeUrlsToCasino_casinoPageExists_ShouldChangeToCasinoWithLanguage(): void
    {
        $_SERVER['REQUEST_URI'] = '/fr';

        $casinoPage = new class {
            public $id = 1;
            public $post_name = 'casino';
        };

        $functionExistsMock = $this->getFunctionMock(
            'Helpers',
            'function_exists'
        );

        $getPostIdBySlugMock = $this->getFunctionMock(
            'Helpers',
            self::LOTTO_PLATFORM_GET_POST_ID_BY_SLUG
        );

        $getPostMock = $this->getFunctionMock(
            'Helpers',
            'get_post'
        );

        $functionExistsMock->expects($this->once())
            ->with(self::LOTTO_PLATFORM_GET_POST_ID_BY_SLUG)->willReturn(true);

        $getPostIdBySlugMock->expects($this->once())
            ->with('casino')->willReturn($casinoPage->id);

        $getPostMock->expects($this->once())
            ->with($casinoPage->id)->willReturn($casinoPage);

        $body = '<div class="some-html-content"><a href="https://lottopark.loc/casino"></a></div>';
        $expected = '<div class="some-html-content"><a href="https://casino.lottopark.loc/fr/"></a></div>';

        $changedBody = UrlHelper::changeUrlsToCasino($body);

        $this->assertEquals($expected, $changedBody);
    }

    /** @test */
    public function changeUrlsToCasino_casinoPageNotExist_ShouldNotChangeUrl(): void
    {
        $_SERVER['REQUEST_URI'] = '/fr';

        $functionExistsMock = $this->getFunctionMock(
            'Helpers',
            'function_exists'
        );

        $getPostIdBySlugMock = $this->getFunctionMock(
            'Helpers',
            self::LOTTO_PLATFORM_GET_POST_ID_BY_SLUG
        );

        $getPostMock = $this->getFunctionMock(
            'Helpers',
            'get_post'
        );

        $functionExistsMock->expects($this->once())
            ->with(self::LOTTO_PLATFORM_GET_POST_ID_BY_SLUG)->willReturn(true);

        $getPostIdBySlugMock->expects($this->once())
            ->with('casino');

        $getPostMock->expects($this->never());

        $body = '<div class="some-html-content"><a href="https://lottopark.loc/casino"></a></div>';
        $expected = $body;

        $changedBody = UrlHelper::changeUrlsToCasino($body);

        $this->assertSame($expected, $changedBody);
    }

    /** @test */
    public function changeAbsoluteUrlToCasinoUrl_isCasino()
    {
        $whitelabelDomain = Lotto_Helper::getWhitelabelDomainFromUrl();
        $exampleUrl = "https://{$whitelabelDomain}/1/2";
        $changedUrl = UrlHelper::changeAbsoluteUrlToCasinoUrl($exampleUrl, true);

        $this->assertEquals("https://casino.{$whitelabelDomain}/1/2", $changedUrl);
    }

    /** @test */
    public function changeAbsoluteUrlToCasinoUrl_isNotCasino()
    {
        $whitelabelDomain = Lotto_Helper::getWhitelabelDomainFromUrl();
        $exampleUrl = "https://{$whitelabelDomain}/1/2";
        $changedUrl = UrlHelper::changeAbsoluteUrlToCasinoUrl($exampleUrl);

        $this->assertEquals("https://{$whitelabelDomain}/1/2", $changedUrl);
    }

    /** @test */
    public function changeAbsoluteUrlToCasinoUrlWWW_isCasino()
    {
        $whitelabelDomain = Lotto_Helper::getWhitelabelDomainFromUrl();
        $exampleUrl = "https://www.{$whitelabelDomain}/1/2";
        $changedUrl = UrlHelper::changeAbsoluteUrlToCasinoUrl($exampleUrl, true);

        $this->assertEquals("https://casino.$whitelabelDomain/1/2", $changedUrl);
    }

    /** @test */
    public function changeAbsoluteUrlToCasinoUrlWWW_isNotCasino()
    {
        $whitelabelDomain = Lotto_Helper::getWhitelabelDomainFromUrl();
        $exampleUrl = "https://www.{$whitelabelDomain}/1/2";
        $changedUrl = UrlHelper::changeAbsoluteUrlToCasinoUrl($exampleUrl);

        $this->assertEquals("https://www.{$whitelabelDomain}/1/2", $changedUrl);
    }

    /** @test */
    public function removeCasinoPrefixFromAbsoluteUrl()
    {
        $exampleUrl = 'https://casino.lovcasino.loc/test';
        $changedUrl = UrlHelper::removeCasinoPrefixFromAbsoluteUrl($exampleUrl);

        $this->assertEquals('https://lovcasino.loc/test', $changedUrl);
    }

    /** @test */
    public function addWwwPrefixIfNeeded_forLottopark_withoutWwww()
    {
        $exampleUrl = 'https://lottopark.com/test';
        $changedUrl = UrlHelper::addWwwPrefixIfNeeded($exampleUrl);
        $expectedUrl = 'https://lottopark.com/test';

        $this->assertEquals($expectedUrl, $changedUrl);
    }

    /** @test */
    public function addWwwPrefixIfNeeded_forLottoparkCasino_withoutWwww()
    {
        $exampleUrl = 'https://casino.lottopark.com/test';
        $changedUrl = UrlHelper::addWwwPrefixIfNeeded($exampleUrl);
        $expectedUrl = 'https://casino.lottopark.com/test';

        $this->assertEquals($expectedUrl, $changedUrl);
    }

    /** @test */
    public function addWwwPrefixIfNeeded_forLottohoy_withWwww()
    {
        $exampleUrl = 'https://lottohoy.com/test';
        $changedUrl = UrlHelper::addWwwPrefixIfNeeded($exampleUrl);
        $expectedUrl = 'https://www.lottohoy.com/test';

        $this->assertEquals($expectedUrl, $changedUrl);
    }

    /** @test */
    public function addWwwPrefixIfNeeded_forLottohoyCasino_shouldNotHaveWww()
    {
        $_SERVER['HTTP_HOST'] = 'casino.lottohoy.com';
        $exampleUrl = 'https://casino.lottohoy.com/test';
        $changedUrl = UrlHelper::addWwwPrefixIfNeeded($exampleUrl);
        $expectedUrl = 'https://casino.lottohoy.com/test';

        $this->assertEquals($expectedUrl, $changedUrl);
    }

    /** @test */
    public function getHomeUrlWithoutLanguage_forCasino(): void
    {
        $_SERVER['HTTP_HOST'] = 'casino.lottohoy.com';
        $url = UrlHelper::getHomeUrlWithoutLanguage('/test');
        $this->assertEquals('https://casino.lottohoy.com/test', $url);
    }

    /** @test */
    public function getSignUpPageUrlWithoutLanguage(): void
    {
        $_SERVER['HTTP_HOST'] = 'casino.lottohoy.com';
        $url = UrlHelper::getSignUpUrlInDefaultLanguage();
        $this->assertSame('https://casino.lottohoy.com/auth/signup', $url);
    }

    /** @test */
    public function getSignUpPageUrlWithoutLanguage_withoutSubDomain(): void
    {
        $_SERVER['HTTP_HOST'] = 'lottopark.com';
        $url = UrlHelper::getSignUpUrlInDefaultLanguage();
        $this->assertSame('https://lottopark.com/auth/signup', $url);
    }

    /** @test */
    public function getSignUpPageUrlWithoutLanguage_domainContainWww(): void
    {
        $_SERVER['HTTP_HOST'] = 'www.lottohoy.com';
        $url = UrlHelper::getSignUpUrlInDefaultLanguage();
        $this->assertSame('https://www.lottohoy.com/auth/signup', $url);

        $_SERVER['HTTP_HOST'] = 'lottohoy.com';
        $url = UrlHelper::getSignUpUrlInDefaultLanguage();
        $this->assertSame('https://www.lottohoy.com/auth/signup', $url);
    }

    /** @test */
    public function getHomeUrlWithoutLanguage_withDomainArg(): void
    {
        $_SERVER['HTTP_HOST'] = 'casino.lottohoy.com';
        $url = UrlHelper::getHomeUrlWithoutLanguage('/test', 'test.domain');
        $this->assertEquals('https://test.domain/test', $url);
    }

    /** @test */
    public function getHomeUrlWithoutLanguage_withEmptyDomain(): void
    {
        $_SERVER['HTTP_HOST'] = null;
        $whitelabel = $this->container->get('whitelabel');
        $previousDomain = $whitelabel->domain;
        $whitelabel->domain = null;

        $url = UrlHelper::getHomeUrlWithoutLanguage('/test');
        $this->assertEquals('', $url);

        $whitelabel->domain = $previousDomain;
    }

    /** @test
     *  @dataProvider provideQueryStringData
     */
    public function parseUrlParametersFromQueryString(array $expectedArray, string $queryString): void
    {
        $resultArray = UrlHelper::parseUrlQueryStringAsArray($queryString);
        $this->assertEquals($expectedArray, $resultArray);
    }

    public static function provideQueryStringData(): array
    {
        return [
            [
                [
                    'tradeNo' => 'T0123456789',
                    'merNo' => '12345',
                    'gatewayNo' => '1234567890',
                    'orderNo' => 'LPD644678057',
                    'orderAmount' => '2.65',
                ],
                'tradeNo=T0123456789&merNo=12345&gatewayNo=1234567890&orderNo=LPD644678057&orderAmount=2.65'
            ],
            [
                [
                    'tradeNo' => 'T0123456789',
                    'merNo' => '12345',
                    'gatewayNo' => '1234567890',
                    'orderNo' => '', // Empty parameter
                    'orderAmount' => '2.65',
                ],
                'tradeNo=T0123456789&merNo=12345&gatewayNo=1234567890&orderNo=&orderAmount=2.65'
            ],
            [
                [], // No parameters
                ''
            ]
        ];
    }

    /**
     * @test
     * @dataProvider getCurrentSubdomainProvider
     */
    public function getCurrentSubdomain(string $domain, ?string $expect): void
    {
        $_SERVER['HTTP_HOST'] = $domain;
        $subdomain = UrlHelper::getCurrentSubdomain();
        $this->assertSame($expect, $subdomain);
    }

    public function getCurrentSubdomainProvider(): array
    {
        return [
            ['aff.lottopark.com', 'aff'],
            ['aff.pre-master.lottopark.com', 'aff'],
            ['lottopark.com', null],
            ['casino.lottopark.work', 'casino'],
            ['www.lottohoy.com', 'www']
        ];
    }

    /**
     * @test
     * @dataProvider getCurrentCasinoPrefixProvider
     */
    public function getCurrentCasinoPrefix(string $domain, string $expect): void
    {
        $_SERVER['HTTP_HOST'] = $domain;
        $prefix = UrlHelper::getCurrentCasinoPrefix();
        $this->assertSame($expect, $prefix);
    }

    public function getCurrentCasinoPrefixProvider(): array
    {
        return [
            ['aff.lottopark.com', 'casino'],
            ['casino.lottopark.work', 'casino'],
            ['casino.faireum.win', 'casino'],
        ];
    }

    /**
     * @test
     * @dataProvider getCasinoPrefixForWhitelabelProvider
     */
    public function getCasinoPrefixForWhitelabel(string $domain, string $expect): void
    {
        $prefix = UrlHelper::getCasinoPrefixForWhitelabel($domain);
        $this->assertSame($expect, $prefix);
    }

    public function getCasinoPrefixForWhitelabelProvider(): array
    {
        return [
            ['something.notexisted', 'casino'],
        ];
    }

    /** @test */
    public function getCasinoPrefixesAsRegex(): void
    {
        $prefixes = UrlHelper::getCasinoPrefixesAsRegex();
        $prefixesCount = count(UrlHelper::getCasinoPrefixes());
        if ($prefixesCount > 1) {
            $this->assertStringEndsWith('|casino', $prefixes);
        } else {
            $this->assertStringEndsWith('casino', $prefixes);
        }
    }

    /**
     * @test
     * @dataProvider getCurrentUrlWithoutParamsProvider
     */
    public function getCurrentUrlWithoutParams(
        bool $isHttps,
        string $httpHost,
        string $requestUri,
        string $expectedUrl
    ): void {
        $_SERVER['HTTPS'] = $isHttps;
        $_SERVER['HTTP_HOST'] = $httpHost;
        $_SERVER['REQUEST_URI'] = $requestUri;
        $actual = UrlHelper::getCurrentUrlWithoutParams();
        $this->assertSame($expectedUrl, $actual);
    }

    public function getCurrentUrlWithoutParamsProvider(): array
    {
        return [
            [false, 'lottopark.loc', '/pl/auth/signup', 'http://lottopark.loc/pl/auth/signup'],
            [true, 'www.lottopark.loc', '/en/', 'https://www.lottopark.loc/en/'],
        ];
    }

    /** @test */
    public function redirectToHomepage(): void
    {
        $this->expectException(PhpErrorException::class);
        $this->expectExceptionMessage('Cannot modify header information');

        $this->lottoPlatformHomeUrlMock->expects($this->any())
            ->willReturnOnConsecutiveCalls('https://lottopark.com/', 'https://casino.lottopark.com/');

        UrlHelper::redirectToHomepage(false);
        UrlHelper::redirectToHomepage(false);
    }

    public function domainAndUrlProvider(): array
    {
        return [
            ['lottopark.com', 'https://lottopark.com/en/play', 'https://lottopark.com/en/play'],
            ['redfoxlotto.com', 'https://lottopark.com/en/play', 'https://redfoxlotto.com/en/play'],
            ['www.lottohoy.com', 'https://lottopark.com/en/play', 'https://www.lottohoy.com/en/play'],
            ['casino.lottopark.com', 'https://casino.lottopark.com/?type=slots', 'https://casino.lottopark.com/?type=slots'],
            ['casino.lottobazar.com', 'https://casino.lottopark.com/?type=slots', 'https://casino.lottobazar.com/?type=slots'],
        ];
    }

    /**
     * @test
     * @dataProvider domainAndUrlProvider
     */
    public function checkAndChangeWhitelabelDomainInUrl(string $domain, string $url, string $expected): void
    {
        $actual = UrlHelper::checkAndChangeWhitelabelDomainInUrl($domain, $url);
        $this->assertSame($expected, $actual);
    }
}
