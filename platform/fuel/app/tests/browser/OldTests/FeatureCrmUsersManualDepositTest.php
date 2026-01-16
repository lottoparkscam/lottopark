<?php

namespace Tests\Browser\OldTests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium;
use Test\Selenium\Login\SeleniumLoginService;
use Test\Selenium\Table\SeleniumTableHelper;
use Test\Selenium\CRM\User\SeleniumCrmUserHelper;
use Model_Whitelabel_User;
use Model_Currency;
use Model_Language;
use NumberFormatter;
use Helpers_Currency;

class FeatureCrmUsersManualDepositTest extends Selenium
{
    private SeleniumLoginService $loginService;
    private SeleniumTableHelper $tableHelper;
    private SeleniumCrmUserHelper $manualDepositHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginService = new SeleniumLoginService($this->driver);
        $this->tableHelper = new SeleniumTableHelper($this->driver);
        $this->manualDepositHelper = new SeleniumCrmUserHelper($this->driver);
    }

    /**
     * @test
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function changeManualDeposit(): void
    {
        $exampleToken = 'LPU607097733';

        $this->loginService->loginCrmSuperadmin();
        $this->driver->get('https://admin.whitelotto.loc/whitelabel/users');

        $this->tableHelper->setTable(
            '//*[@id="main-wrapper"]/div/div[2]/div/div[2]/div/div/div/div/div[4]/div/table',
            $exampleToken
        );

        // check if Balance select is checked
        $this->manualDepositHelper->addColumnToView('Bonus');

        // check if Token select is checked
        $this->manualDepositHelper->addColumnToView('Token');

        // exclude rows with filters
        $this->tableHelper->excludeFirstRow();
        $this->tableHelper->excludeLastRow();

        $rowsAmount = $this->tableHelper->calcRows();
        $randomRow = rand(0, $rowsAmount - 1);

        // get previous values
        $balance = $this->tableHelper->findContentByRowNumber('Balance', $randomRow);
        $token = $this->tableHelper->findContentByRowNumber('Token', $randomRow);

        $balanceColumnNumber = $this->tableHelper->findColumnNumberByContent('Balance');


        // ------------------------- ADD MANUAL DEPOSIT

        // $randomRow + 1 because first row is filter
        $this->manualDepositHelper->clickManualDeposit($randomRow + 1, $balanceColumnNumber);

        $saveButton = WebDriverBy::xpath('//*[@id="main-wrapper"]/div/div[2]/div/div/div/form/div/div[4]/button');

        $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable($saveButton));

        // chose method
        $this->driver->findElement(
            WebDriverBy::xpath('//*[@id="main-wrapper"]/div/div[2]/div/div/div/form/div/div[2]/div[2]/div/select/option[2]')
        )->click();

        $user = Model_Whitelabel_User::find_one_by('token', substr($token, 3));

        $this->assertIsObject($user);

        $amountInput = $this->driver->findElement(
            WebDriverBy::xpath('//*[@id="single-input"]')
        );
        $amountInput->clear()->sendKeys('30');

        $this->driver->findElement($saveButton)->click();


        // ------------------------- CHECK MANUAL DEPOSIT
        $this->driver->get('https://admin.whitelotto.loc/whitelabel/users');

        $this->tableHelper->reload();

        $newBalance = $this->tableHelper->findContentByRowNumber('Balance', $randomRow);

        $userLanguage = Model_Language::find_by_pk($user['language_id']);
        $userLanguageCode = $userLanguage['code'];

        $managerCurrencyCode = "USD";

        $userCurrency = Model_Currency::find_by_pk($user['currency_id']);

        $formatter = new NumberFormatter($userLanguageCode, NumberFormatter::CURRENCY);

        $balanceCurrencyTab = [
            'id' => $userCurrency['id'],
            'code' => $userCurrency['code'],
            'rate' => $userCurrency['rate']
        ];

        $balanceInManagerCurrency = (float)Helpers_Currency::get_recalculated_to_given_currency(
            $user['balance'] + 30,
            $balanceCurrencyTab,
            $managerCurrencyCode
        );

        $balanceFormatted = $formatter->formatCurrency($balanceInManagerCurrency, $managerCurrencyCode);

        $this->assertNotEquals($newBalance, $balance);
        $this->assertEquals($newBalance, $balanceFormatted);
    }

    /**
     * @test
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function changeManualDepositBonusBalance()
    {
        $exampleToken = 'LPU607097733';

        $this->loginService->loginCrmSuperadmin();
        $this->driver->get('https://admin.whitelotto.loc/whitelabel/users');

        $this->tableHelper->setTable(
            '//*[@id="main-wrapper"]/div/div[2]/div/div[2]/div/div/div/div/div[4]/div/table',
            $exampleToken
        );

        // check if Bonus balance select is checked
        $this->manualDepositHelper->addColumnToView('Bonus balance');

        // check if Token select is checked
        $this->manualDepositHelper->addColumnToView('Token');

        // exclude rows with filters
        $this->tableHelper->excludeFirstRow();
        $this->tableHelper->excludeLastRow();

        $rowsAmount = $this->tableHelper->calcRows();
        $randomRow = rand(0, $rowsAmount - 1);

        // get previous values
        $bonusBalance = $this->tableHelper->findContentByRowNumber('Bonus balance', $randomRow);
        $token = $this->tableHelper->findContentByRowNumber('Token', $randomRow);

        $bonusBalanceColumnNumber = $this->tableHelper->findColumnNumberByContent('Bonus balance');


        // ------------------------- ADD MANUAL DEPOSIT

        // $randomRow + 1 because first row is filter
        $this->manualDepositHelper->clickManualDeposit($randomRow + 1, $bonusBalanceColumnNumber);

        $saveButton = WebDriverBy::xpath('//*[@id="main-wrapper"]/div/div[2]/div/div/div/form/div/div[4]/button');

        $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable($saveButton));

        // chose method
        $this->driver->findElement(
            WebDriverBy::xpath('//*[@id="main-wrapper"]/div/div[2]/div/div/div/form/div/div[2]/div[2]/div/select/option[2]')
        )->click();

        $user = Model_Whitelabel_User::find_one_by('token', substr($token, 3));

        $this->assertIsObject($user);

        $amountInput = $this->driver->findElement(
            WebDriverBy::xpath('//*[@id="single-input"]')
        );
        $amountInput->clear()->sendKeys('30');

        $this->driver->findElement($saveButton)->click();


        // ------------------------- CHECK MANUAL DEPOSIT
        $this->driver->get('https://admin.whitelotto.loc/whitelabel/users');

        $this->tableHelper->reload();

        $newBonusBalance = $this->tableHelper->findContentByRowNumber('Bonus balance', $randomRow);

        $userLanguage = Model_Language::find_by_pk($user['language_id']);
        $userLanguageCode = $userLanguage['code'];

        $managerCurrencyCode = "USD";

        $userCurrency = Model_Currency::find_by_pk($user['currency_id']);

        $formatter = new NumberFormatter($userLanguageCode, NumberFormatter::CURRENCY);

        $bonusBalanceCurrencyTab = [
            'id' => $userCurrency['id'],
            'code' => $userCurrency['code'],
            'rate' => $userCurrency['rate']
        ];

        $bonusBalanceInManagerCurrency = (float)Helpers_Currency::get_recalculated_to_given_currency(
            $user['bonus_balance'] + 30,
            $bonusBalanceCurrencyTab,
            $managerCurrencyCode
        );

        $bonusBalanceFormatted = $formatter->formatCurrency($bonusBalanceInManagerCurrency, $managerCurrencyCode);

        $this->assertNotEquals($newBonusBalance, $bonusBalance);
        $this->assertEquals($newBonusBalance, $bonusBalanceFormatted);
    }
}