<?php

namespace Tests\Browser\Crm;

use Lotto_Helper;
use Fuel\Core\Cache;
use Test\Selenium\Login\SeleniumLoginService;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;
use Test\Selenium\Blocked\SeleniumBlockedCountryService;
use Test\Selenium\Interfaces\CrmInterface;

final class BlockedCountryTest extends AbstractSeleniumPageBase implements CrmInterface
{
    /** @var string[] $countries */
    private $countries = [];
    protected SeleniumBlockedCountryService $blockedHelper;
    protected SeleniumLoginService $loginService;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::delete_all();

        /** @var array $countries */
        $this->countries = Lotto_Helper::get_localized_country_list() ?? [];
        $this->blockedService = new SeleniumBlockedCountryService($this->driver);
        $this->loginService = new SeleniumLoginService($this->driver);
        $this->blockedHelper = new SeleniumBlockedCountryService($this->driver);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /** @test */
    public function superuserCanAddAndDelete_BlockedCountry(): void
    {
        $this->loginService->loginManagerSuperadmin();

        $randomCountries = $this->blockedService->addRandomCountries($this->countries);
        $this->blockedService->checkIfCountriesExist($randomCountries, $this);
        $this->blockedService->deleteAllCountries();
        $this->blockedService->checkIfCountriesNoExist($randomCountries, $this);
    }

    /** @test */
    public function lottoparkUserCanAddAndDelete_blockedCountry(): void
    {
        $this->loginService->loginManagerLottopark();

        $randomCountries = $this->blockedService->addRandomCountries($this->countries);
        $this->blockedService->checkIfCountriesExist($randomCountries, $this);
        $this->blockedService->deleteAllCountries();
        $this->blockedService->checkIfCountriesNoExist($randomCountries, $this);
    }

    /** @test */
    public function deleteNoDeletable_BlockedCountry_BySuperuser(): void
    {
        $this->loginService->loginManagerLottopark();
        $randomCountries = $this->blockedHelper->addRandomCountries($this->countries);

        // block all countries against deletion
        $this->blockedService->blockAllCountries();
        $this->blockedService->deleteAllCountries();
        $this->blockedService->checkIfCountriesNoExist($randomCountries, $this);
    }

    /** @test */
    public function deleteNoDeletable_BlockedCountry_ByLottoparkUser(): void
    {
        $this->loginService->loginManagerLottopark();
        $randomCountries = $this->blockedService->addRandomCountries($this->countries);

        // block all countries against deletion
        $this->blockedService->blockAllCountries();

        $this->loginService->logout();
        $this->loginService->loginManagerLottopark();
        $this->blockedService->getBlockedCountryPage();

        $this->blockedService->deleteAllCountries();
        $this->blockedService->checkIfCountriesExist($randomCountries, $this);

        $this->loginService->logout();
        $this->loginService->loginManagerLottopark();
        $this->blockedService->getBlockedCountryPage();
        $this->blockedService->deleteAllCountries();
    }
}
