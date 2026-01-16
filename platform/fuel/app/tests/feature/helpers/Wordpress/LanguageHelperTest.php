<?php

namespace Feature\Helpers\Wordpress;

use Abstracts\Controllers\Internal\AbstractPublicController;
use Container;
use Helpers\Wordpress\LanguageHelper;
use Helpers_App;
use Lotto_Helper;
use Models\WhitelabelLanguage;
use Models\WhitelabelUser;
use phpmock\phpunit\PHPMock;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelLanguageRepository;
use Test_Feature;
use Tests\Fixtures\WhitelabelUserFixture;
use Wrappers\Db;

class LanguageHelperTest extends Test_Feature
{
    use PHPMock;

    private WhitelabelLanguageRepository $whitelabelLanguageRepository;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private WhitelabelUser $whitelabelUser;
    private Db $db;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelLanguageRepository = Container::get(WhitelabelLanguageRepository::class);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);

        $this->whitelabelUserFixture = Container::get(WhitelabelUserFixture::class);
        $userFromFixture = $this->whitelabelUserFixture->with(WhitelabelUserFixture::BASIC)
            ->createOne();
        /** @var WhitelabelUser $user */
        $this->whitelabelUser = $this->whitelabelUserRepository->findOneById($userFromFixture->id);

        $this->applyFiltersMock = $this->getFunctionMock('Helpers\Wordpress', 'apply_filters');
        $wpmlActiveLanguagesJsonPath = Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/Helpers/Wordpress/WpmlActiveLanguages.json');
        $wpmlActiveLanguages = json_decode(file_get_contents($wpmlActiveLanguagesJsonPath), true);
        $this->applyFiltersMock->expects($this->any())->willReturn($wpmlActiveLanguages);
        $this->db = Container::get(Db::class);
    }

    /** @test */
    public function getCurrentLanguageShortcode_languageInUrlExists(): void
    {
        global $sitepress;
        $sitepress = new class ()
        {
            public function get_language_from_url()
            {
                return 'pl';
            }
        };

        $expected = 'pl';
        $actual = LanguageHelper::getCurrentLanguageShortcode();
        $this->assertSame($expected, $actual);
        $this->assertFalse(defined('WP_CLI'));
    }

    /** @test */
    public function getCurrentWhitelabelLanguage_languageInUrlExists(): void
    {
        global $sitepress;
        $sitepress = new class ()
        {
            public function get_language_from_url()
            {
                return 'pl';
            }
        };

        $actual = LanguageHelper::getCurrentWhitelabelLanguage();
        /** @var WhitelabelLanguage $whitelabelLanguage */
        $whitelabelLanguage = $this->whitelabelLanguageRepository->findOneById(2);
        $expected = [
            'id' => (string)$whitelabelLanguage->language->id,
            'default_currency_id' => (string)$whitelabelLanguage->language->defaultCurrencyId,
            'code' => $whitelabelLanguage->language->code,
            'js_currency_format' => $whitelabelLanguage->language->jsCurrencyFormat,
            'wl_lang_id' => (string)$whitelabelLanguage->id,
            'currency_id' => (string)$whitelabelLanguage->currencyId,
            'full_code' => $whitelabelLanguage->language->code . '.utf8',
        ];
        $this->assertSame($expected, $actual);
        $this->assertSame('pl_PL', $whitelabelLanguage->language->code);
        $this->assertFalse(defined('WP_CLI'));
    }

    /** @test */
    public function getDefaultWhitelabelLanguageShortcode_default(): void
    {
        /** @var WhitelabelLanguage[] $allWhitelabelLanguages */
        $allWhitelabelLanguages = $this->whitelabelLanguageRepository->getResults();
        foreach ($allWhitelabelLanguages as $whitelabelLanguage) {
            $whitelabelLanguage->delete();
        }
        $actual = LanguageHelper::getDefaultWhitelabelLanguageShortcode();
        $this->assertSame(0, $this->whitelabelLanguageRepository->getCount());
        $this->assertSame(LanguageHelper::DEFAULT_LANGUAGE_SHORTCODE, $actual);
    }

    /** @test */
    public function getDefaultWhitelabelLanguageShortcode_firstFromList(): void
    {
        global $sitepress;
        $sitepress = new class ()
        {
            public function get_language_from_url()
            {
                return 'en';
            }
        };

        $actual = LanguageHelper::getCurrentLanguageShortcode();
        $this->assertSame(2, $this->whitelabelLanguageRepository->getCount());
        $this->assertSame(LanguageHelper::DEFAULT_LANGUAGE_SHORTCODE, $actual);

        /** @var WhitelabelLanguage[] $allWhitelabelLanguages */
        $allWhitelabelLanguages = $this->whitelabelLanguageRepository->getResults();
        foreach ($allWhitelabelLanguages as $whitelabelLanguage) {
            $isEnglish = $whitelabelLanguage->languageId === 1;
            if ($isEnglish) {
                // To force polish as a default cause will be first language in id order
                $whitelabelLanguage->languageId = 3;
                $whitelabelLanguage->save();
            }
        }

        Lotto_Helper::clear_cache();
        $actual = LanguageHelper::getDefaultWhitelabelLanguageShortcode();
        $this->assertSame(2, $this->whitelabelLanguageRepository->getCount());
        $this->assertSame('pl', $actual);
    }

    /** @test */
    public function getCurrentLanguageShortcode_noSitepress(): void
    {
        $_SERVER['REQUEST_URI'] = '/pl/test';
        $actual = LanguageHelper::getCurrentLanguageShortcode();
        $expected = 'pl';
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getCurrentWhitelabelLanguage_noSitepress(): void
    {
        $_SERVER['REQUEST_URI'] = '/pl/test';
        $actual = LanguageHelper::getCurrentWhitelabelLanguage();
        $expected = 'pl_PL';
        $this->assertSame($expected, $actual['code']);
    }

    /** @test */
    public function getCurrentWhitelabelLanguage_apiLanguageDefined(): void
    {
        $_SERVER['REQUEST_URI'] = '/test';
        define(AbstractPublicController::API_LANGUAGE_SHORTCODE, 'pl');
        $actual = LanguageHelper::getCurrentWhitelabelLanguage();
        $expected = 'pl_PL';
        $this->assertSame($expected, $actual['code']);
    }

    /**
     * @test
     * @dataProvider getLocalesProvider
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function getLanguageNameByLocale(string $testedLocale, ?string $languageToDisplay, string $expected): void
    {
        $actual = LanguageHelper::getLanguageNameByLocale($testedLocale, $languageToDisplay);
        $this->assertSame($expected, $actual);
    }

    public function getLocalesProvider(): array
    {
        return [
            ['pl_PL', 'pl_PL', 'Polski'],
            ['pt_PT', 'pl_PL', 'Portugalski (Portugalia)'],
            ['cc_CC', 'pl_PL', 'cc_CC'], // not valid locale should return just locale
            ['pt_BR', 'pl_PL', 'Portugalski (Brazylia)'],
            ['pl_PL', 'de_DE', 'Polnisch'],
            ['pl_PL', '', 'Polish'], // default is en
            ['pl_PL', 'zh_CN', '波兰语'], // check different charset
            ['pl_PL', 'ko_KO', '폴란드어'], // check different charset
            ['pl_PL', 'ar_AR', 'البولندية'], // check LTR lang
        ];
    }

    /** @test */
    public function getCurrentLanguageShortcode(): void
    {
        $this->assertSame('en', LanguageHelper::getShortcodeLanguage('en_GB'));
        $this->assertSame('pl', LanguageHelper::getShortcodeLanguage('pl_PL'));
        $this->assertSame('cs', LanguageHelper::getShortcodeLanguage('cs_CZ'));
        $this->assertSame('hr', LanguageHelper::getShortcodeLanguage('hr_HR'));
    }
}
