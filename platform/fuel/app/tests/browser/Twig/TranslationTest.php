<?php

namespace Browser\Twig;

use Test\Selenium;

final class TranslationTest extends Selenium
{
    /** @test */
    public function twigDetectsLanguage(): void
    {
        $this->driver->get('https://lottopark.loc/pl/zaloguj-sie/');
        $expectedPolishTranslation = 'Nie masz jeszcze konta? Zarejestruj się.';
        $actual = $this->findByCssSelector('body form.platform-form.platform-form-login p.text-center')->getText();
        $this->assertSame($expectedPolishTranslation, $actual);
    }

    /** @test */
    public function twigDetectsHrefForSpecificLanguage()
    {
        $this->driver->get('https://lottopark.loc/pl/zaloguj-sie/');
        $expectedPolishUrl = 'https://lottopark.loc/pl/rejestracja-otworz-konto/';
        $actual = $this->getHref($this->findByCssSelector('body form.platform-form.platform-form-login p.text-center a'));
        $this->assertSame($expectedPolishUrl, $actual);
    }
}
