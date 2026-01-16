<?php

namespace Tests\Browser\Manager;

use Lotto_Helper;
use Test\Selenium\Login\SeleniumLoginService;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;
use Test\Selenium\Blocked\SeleniumBlockedCountryService;

final class BlockedCountryTest extends AbstractSeleniumPageBase
{
    /** @var string[] $countries */
    private $countries = [];
    private SeleniumBlockedCountryService $blockedHelper;
    private SeleniumLoginService $loginService;

    public function setUp(): void
    {
        parent::setUp();

        /** @var array $countries */
        $this->countries = Lotto_Helper::get_localized_country_list() ?? [];
        $this->blockedHelper = new SeleniumBlockedCountryService($this->driver);
        $this->loginService = new SeleniumLoginService($this->driver);
    }

    /** @test */
    public function superUser_CanAddAndDeleteBlockedCountry(): void
    {
        $this->loginService->loginManagerSuperadmin();

        $randomCountries = $this->blockedHelper->addRandomCountries($this->countries);
        $this->blockedHelper->checkIfCountriesExist($randomCountries, $this);
        $this->blockedHelper->deleteAllCountries();
        $this->blockedHelper->checkIfCountriesNoExist($randomCountries, $this);
    }

    /** @test */
    public function lottoparkUser_CanAddAndDeleteBlockedCountry(): void
    {
        $this->loginService->loginManagerLottopark();

        $randomCountries = $this->blockedHelper->addRandomCountries($this->countries);
        $this->blockedHelper->checkIfCountriesExist($randomCountries, $this);
        $this->blockedHelper->deleteAllCountries();
        $this->blockedHelper->checkIfCountriesNoExist($randomCountries, $this);
    }

    /** @test */
    public function superUser_DeleteNoDeletableBlockedCountry(): void
    {
        $this->loginService->loginManagerLottopark();
        $randomCountries = $this->blockedHelper->addRandomCountries($this->countries);

        // block all countries against deletion
        $this->blockedHelper->blockAllCountries();
        $this->blockedHelper->deleteAllCountries();
        $this->blockedHelper->checkIfCountriesNoExist($randomCountries, $this);
    }

    /** @test */
    public function deleteNoDeletable_BlockedCountry_ByLottoparkUser(): void
    {
        $this->loginService->loginManagerLottopark();
        $randomCountries = $this->blockedHelper->addRandomCountries($this->countries);

        // block all countries against deletion
        $this->blockedHelper->blockAllCountries();

        $this->loginService->logout();
        $this->loginService->loginManagerLottopark();
        $this->blockedHelper->getBlockedCountryPage();

        $this->blockedHelper->deleteAllCountries();
        $this->blockedHelper->checkIfCountriesExist($randomCountries, $this);

        $this->loginService->logout();
        $this->loginService->loginManagerLottopark();
        $this->blockedHelper->getBlockedCountryPage();
        $this->blockedHelper->deleteAllCountries();
    }
}
