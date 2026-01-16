<?php

namespace Tests\Browser\User;

use Container;
use Lotto_View;
use IntlDateFormatter;
use Test\Selenium\SeleniumUserService;
use Test\Selenium\SeleniumGlobalService;
use Test\Selenium\SeleniumTicketService;
use Tests\Fixtures\WhitelabelUserFixture;
use Test\Selenium\Interfaces\UserInterface;
use Test\Selenium\Login\SeleniumLoginService;
use Test\Selenium\Interfaces\EurojackpotInterface;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;

final class TransactionsTest extends AbstractSeleniumPageBase implements UserInterface, EurojackpotInterface
{
    private SeleniumLoginService $loginService;
    private SeleniumGlobalService $globalService;
    private SeleniumTicketService $ticketService;
    private SeleniumUserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginService = new SeleniumLoginService($this->driver);
        $this->globalService = new SeleniumGlobalService($this->driver);
        $this->ticketService = new SeleniumTicketService($this->driver);
        $this->userService = new SeleniumUserService($this->driver);
        $fixtureUser = Container::get(WhitelabelUserFixture::class);
        $fixtureUser->addUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->userService->deleteUser(self::TEST_USER_EMAIL);
    }

    /** @test */
    public function transactionList_haveCorrectData(): void
    {
        $this->loginService->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        //TODO Use Fixtures to create User ticket
        $this->ticketService->buyQuickPickTicket(self::EUROJACKPOT_PLAY_URL);
        $transactionData = $this->globalService->getTransactionByUserEmail(self::TEST_USER_EMAIL);
        $confirmedDate = date_create($transactionData['date_confirmed']);
        $confirmedDate = date_format($confirmedDate, 'n\/j\/y, g:i A');
        $this->driver->get($this->appUrl() . 'account/transactions/');

        $transactionListCssAndText = [
            'h1.account' => 'Account - My transactions',
            'div.myaccount-balance.pull-left' => 'Account balance: €' . number_format($this->userService->getLastUser()['balance'], 2),
            'th.text-left' => 'Transaction ID',
            'th.tablesorter-header.tablesorter-headerUnSorted' => 'Amount',
            'th.tablesorter-header.tablesorter-headerDesc' => 'Date (confirmed)',
            'th:nth-child(4)' => 'Method',
            'th:nth-child(5)' => 'Status',
            'td.transactions-id' => 'LPP' . $transactionData['token'],
            'span.transactions-amount' => '€' . number_format($transactionData['amount'], 2),
            'td.text-center.transactions-date' => $confirmedDate . "\n" . '(' . $confirmedDate . ')',
            'td.transactions-method.text-center' => 'Balance',
            'span.transactions-status.transactions-status-1' => 'approved',
            'div.myaccount-content > div > a' => 'Withdrawal'
        ];

        foreach ($transactionListCssAndText as $key => $value) {
            $this->elementHasText(
                $key,
                $value
            );
        }
    }

    /** @test */
    public function checkTransactionDetails_isCorrect(): void
    {
        $this->userService->updateUserTimezone(self::TEST_USER_EMAIL, self::EUROJACKPOT_TIMEZONE);
        $this->loginService->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->ticketService->buyQuickPickTicket(self::EUROJACKPOT_PLAY_URL);
        $transactionData = $this->globalService->getTransactionByUserEmail(self::TEST_USER_EMAIL);
        date_default_timezone_set(self::EUROJACKPOT_TIMEZONE);
        $purchaseDate = Lotto_View::format_date(
            $transactionData['date'],
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG,
            self::EUROJACKPOT_TIMEZONE,
            true
        );

        $confirmedDate = Lotto_View::format_date(
            $transactionData['date_confirmed'],
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG,
            self::EUROJACKPOT_TIMEZONE,
            true
        );
        $this->driver->get($this->appUrl() . 'account/transactions/' . $transactionData['token']);

        $transactionDetailsCssAndText = [
            'h1.account' => 'Transaction LPP' . $transactionData['token'],
            'div.myaccount-transactions > span:nth-child(1)' => 'Status:',
            'div.myaccount-transactions > span:nth-child(4)' => 'Date:',
            'div.myaccount-transactions > span:nth-child(7)' => 'Confirmation date:',
            'div.myaccount-transactions > span:nth-child(10)' => 'Payment method:',
            'div.myaccount-transactions > span:nth-child(13)' => 'Amount:',
            'div.myaccount-transactions > span:nth-child(2)' => 'approved',
            'div.myaccount-transactions > span:nth-child(5)' => $purchaseDate,
            'div.myaccount-transactions > span:nth-child(8)' => $confirmedDate,
            'div.myaccount-transactions > span:nth-child(11)' => 'Balance',
            'div.myaccount-transactions > span:nth-child(14)' => '€' . number_format($transactionData['amount'], 2),
            'div.header-transaction' => 'Transaction details',
            'span.order-summary-content-header' => 'Eurojackpot ticket',
            'td.text-right.col-amount' => '€' . number_format($transactionData['amount'], 2),
            'div.myaccount-content > div > table > tfoot > tr > td' => 'Total Sum: €' . number_format($transactionData['amount'], 2)
        ];

        $this->globalService->clickElement('span.fa.fa-search');

        foreach ($transactionDetailsCssAndText as $key => $value) {
            $this->elementHasText(
                $key,
                $value
            );
        }
    }
}
