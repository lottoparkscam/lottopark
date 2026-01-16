<?php

namespace Tests\Unit\Helpers;

use Fuel\Core\Input;
use LanguageHelper;
use Test_Unit;

final class LanguageHelperTest extends Test_Unit
{
    private const UNSUPPORTED_LANGUAGE = 'njr';
    private const UNSUPPORTED_LANGUAGE_URI = '/njr';
    private const SUPPORTED_LANGUAGE_URI = '/pl';

    private const ENGLISH_WITH_PAGE_SLUG_IN_URI = '/faq';
    private const SUPPORTED_LANGUAGE_WITH_EXTENDED_URI = '/pl/rejestracja-otworz-konto/asd/asd/asd';

    /** @test */
    public function inputServerHasTheSameValueAsGlobal(): void
    {
        $_SERVER['REQUEST_URI'] = self::UNSUPPORTED_LANGUAGE;
        $this->assertSame(Input::server('REQUEST_URI'), $_SERVER['REQUEST_URI']);
    }

    /** @test */
    public function getLanguageShortcodeFromUrl_LanguageIsNotSupported_ShouldReturnEnglish(): void
    {
        $_SERVER['REQUEST_URI'] = self::UNSUPPORTED_LANGUAGE_URI;
        $this->assertSame('en', LanguageHelper::getLanguageShortcodeFromUrl());
    }

    /** @test */
    public function getLanguageShortcodeFromUrl_UriIsEmpty_ShouldReturnEnglish(): void
    {
        $_SERVER['REQUEST_URI'] = '';
        $this->assertSame('en', LanguageHelper::getLanguageShortcodeFromUrl());
    }

    /** @test */
    public function getLanguageShortcodeFromUrl_IsEnglishWithEmptyUri_ShouldReturnEnglish(): void
    {
        $_SERVER['REQUEST_URI'] = '/';
        $this->assertSame('en', LanguageHelper::getLanguageShortcodeFromUrl());
    }

    /** @test */
    public function getLanguageShortcodeFromUrl_IsEnglishWithPageSlugInUri_ShouldReturnEnglish(): void
    {
        $_SERVER['REQUEST_URI'] = self::ENGLISH_WITH_PAGE_SLUG_IN_URI;
        $this->assertSame('en', LanguageHelper::getLanguageShortcodeFromUrl());
    }

    /** @test */
    public function getLanguageShortcodeFromUrl_IsSupportedLanguage_ShouldReturnValid(): void
    {
        $_SERVER['REQUEST_URI'] = self::SUPPORTED_LANGUAGE_URI;
        $this->assertSame('pl', LanguageHelper::getLanguageShortcodeFromUrl());
    }

    /** @test */
    public function getLanguageShortcodeFromUrl_IsSupportedLanguageWithExtendedUri_ShouldReturnValid(): void
    {
        $_SERVER['REQUEST_URI'] = self::SUPPORTED_LANGUAGE_WITH_EXTENDED_URI;
        $this->assertSame('pl', LanguageHelper::getLanguageShortcodeFromUrl());
    }

    /** @test */
    public function getLanguage_ptPT(): void
    {
        $_SERVER['REQUEST_URI'] = '/pt-pt/rejestracja-otworz-konto/asd/asd/asd';
        $this->assertSame('pt-pt', LanguageHelper::getLanguageShortcodeFromUrl());
        $_SERVER['REQUEST_URI'] = '/pt/rejestracja-otworz-konto/asd/asd/asd';
        $this->assertSame('pt', LanguageHelper::getLanguageShortcodeFromUrl());
    }

    /** @test */
    public function isLanguageSupported_ShouldReturnTrue(): void
    {
        $this->assertTrue(LanguageHelper::isLanguageSupported('pl'));
    }

    /** @test */
    public function isLanguageSupported_IsCaseInsensitive_ShouldReturnTrue(): void
    {
        $this->assertTrue(LanguageHelper::isLanguageSupported('PL'));
    }

    /** @test */
    public function isLanguageSupported_ShouldReturnFalse(): void
    {
        $this->assertFalse(LanguageHelper::isLanguageSupported(self::UNSUPPORTED_LANGUAGE));
    }

    /**
     * @test
     * @dataProvider getLanguageUriProvider
     */
    public function getLanguageUri(string $uri, string $expected): void
    {
        $_SERVER['REQUEST_URI'] = $uri;
        $this->assertSame($expected, LanguageHelper::getLanguageUri());
    }

    public function getLanguageUriProvider(): array
    {
        return [
            'uri is empty should return default language' => ['', '/'],
            'language is not supported should return default language' => [self::UNSUPPORTED_LANGUAGE_URI, '/'],
            'english with page slug in uri should return default language' => [self::ENGLISH_WITH_PAGE_SLUG_IN_URI, '/'],
            'supported language should return current language when exists' => [self::SUPPORTED_LANGUAGE_URI, '/pl'],
            'supported language with extended uri should return current language when exists' => [self::SUPPORTED_LANGUAGE_WITH_EXTENDED_URI, '/pl'],
        ];
    }

    /**
     * @test
     * @dataProvider getLanguageCodeFromLocaleProvider
     */
    public function getLanguageCodeFromLocale(string $testedLocale, string $expected): void
    {
        $actual = LanguageHelper::getLanguageCodeFromLocale($testedLocale);
        $this->assertSame($expected, $actual);
    }

    public function getLanguageCodeFromLocaleProvider(): array
    {
        return [
            ['en_US', 'en'],
            ['pl_PL', 'pl'],
            ['pt_PT', 'pt-pt'],
            ['pt_BR', 'pt'],
            ['notvalid', 'en'],
            ['', 'en'],
            ['fil_PH', 'fil'],
        ];
    }

    /**
     * @test
     * @dataProvider getOnlyCodeAndLocaleProvider
     */
    public function getOnlyCodeAndLocale(string $input, string $expected): void
    {
        $actual = LanguageHelper::getOnlyCodeAndLocale($input);
        $this->assertSame($expected, $actual);
    }

    public function getOnlyCodeAndLocaleProvider(): array
    {
        return [
            'withEncoding' => [
                'ro_RO.utf8',
                'ro_RO'
            ],
            'withoutEncoding' => [
                'en_GB',
                'en_GB'
            ],
            'withEncodingAndLatin' => [
                'ro_RO.utf@latin',
                'ro_RO'
            ],
            'withLatin' => [
                'ro_RO@latin',
                'ro_RO'
            ],
            'withLatinAndEncoding' => [
                'ro_RO@latin.utf8',
                'ro_RO'
            ]
        ];
    }
}
