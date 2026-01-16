<?php

namespace Tests\Browser\User;

use Container;
use Lotto_View;
use IntlDateFormatter;
use Models\WhitelabelUserTicketLine;
use Test\Selenium\SeleniumUserService;
use Test\Selenium\SeleniumGlobalService;
use Test\Selenium\SeleniumTicketService;
use Tests\Fixtures\WhitelabelUserFixture;
use Test\Selenium\Interfaces\UserInterface;
use Test\Selenium\Login\SeleniumLoginService;
use Test\Selenium\Interfaces\EurojackpotInterface;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;

final class TicketTest extends AbstractSeleniumPageBase implements UserInterface, EurojackpotInterface
{
    private SeleniumLoginService $loginService;
    private SeleniumTicketService $ticketService;
    private SeleniumGlobalService $globalService;
    private SeleniumUserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginService = new SeleniumLoginService($this->driver);
        $this->ticketService = new SeleniumTicketService($this->driver);
        $this->globalService = new SeleniumGlobalService($this->driver);
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
    public function ticketList_haveCorrectData(): void
    {
        $this->userService->updateUserTimezone(self::TEST_USER_EMAIL, self::EUROJACKPOT_TIMEZONE);
        $this->loginService->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        //TODO Use Fixtures to create User ticket
        $this->ticketService->buyQuickPickTicket(self::EUROJACKPOT_PLAY_URL);
        $this->driver->get($this->appUrl() . 'account/tickets/awaiting/');

        $ticketData = $this->ticketService->getTicketByLotteryId(3);
        date_default_timezone_set(self::EUROJACKPOT_TIMEZONE);
        $drawDate = Lotto_View::format_date(
            $ticketData['draw_date'],
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG,
            self::EUROJACKPOT_TIMEZONE,
            true
        );

        $myTicketsPageCssAndText = [
            'a.myaccount-tickets-menu-item.active' => 'Upcoming Draws (1)',
            'div.myaccount-content > div > div > a:nth-child(2)' => 'Past Tickets (0)',
            'h1.account' => 'Account - My tickets',
            'th.text-left.tablesorter-header.tablesorter-headerUnSorted' => 'Ticket ID and date',
            'tr > th:nth-child(2)' => 'Lottery name',
            'tr > th:nth-child(3)' => 'Amount',
            'th.tablesorter-header.tablesorter-headerDesc' => 'Draw Date',
            'th.tablesorter-header.tablesorter-0' => 'Status',
            'tr > th:nth-child(6)' => 'Prize',
            'span.tickets-id' => 'LPT' . $ticketData['token'],
            'span.tickets-lottery-name' => 'Eurojackpot',
            'span.transactions-amount' => '€' . number_format($ticketData['amount_local'], 2),
            'td.text-center.transactions-date' => $drawDate,
            'td.transactions-status.transactions-status-0' => 'pending',
        ];

        foreach ($myTicketsPageCssAndText as $key => $value) {
            $this->elementHasText(
                $key,
                $value
            );
        }
    }

    /** @test */
    public function ticketDetails_haveCorrectData(): void
    {
        $this->userService->updateUserTimezone(self::TEST_USER_EMAIL, self::EUROJACKPOT_TIMEZONE);
        $this->loginService->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->ticketService->buyQuickPickTicket(self::EUROJACKPOT_PLAY_URL);
        $this->driver->get($this->appUrl() . 'account/tickets/awaiting/');
        $this->globalService->clickElement('a.tooltip.tooltip-bottom > span');

        $ticketData = $this->ticketService->getTicketByLotteryId(3);
        $transactionData = $this->globalService->getTransactionById($ticketData['whitelabel_transaction_id']);
        date_default_timezone_set(self::EUROJACKPOT_TIMEZONE);
        $purchaseDate = Lotto_View::format_date(
            $ticketData['date'],
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG,
            'GMT',
            true
        );
        $drawDate = Lotto_View::format_date(
            $ticketData['draw_date'],
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG,
            self::EUROJACKPOT_TIMEZONE,
            true
        );

        $ticketDetailsCssAndText = [
            'h1.account' => 'Ticket LPT' . $ticketData['token'],
            'div:nth-child(2) > span:nth-child(1)' => 'Lottery:',
            'div:nth-child(2) > span:nth-child(4)' => 'Status:',
            'div:nth-child(2) > span:nth-child(7)' => 'Draw number:',
            'div:nth-child(2) > span:nth-child(10)' => 'Date:',
            'div:nth-child(2) > span:nth-child(13)' => 'Ticket type:',
            'div:nth-child(2) > span:nth-child(16)' => 'Draw date:',
            'div:nth-child(2) > span:nth-child(19)' => 'Amount:',
            'div:nth-child(2) > span:nth-child(22)' => 'Transaction ID:',
            'span.myaccount-transactions-value.tickets-lottery-name' => 'Eurojackpot',
            'div:nth-child(2) > span:nth-child(5)' => 'purchased',
            'div:nth-child(2) > span:nth-child(11)' => $purchaseDate,
            'div:nth-child(2) > span:nth-child(14)' => 'Single draw',
            'span.myaccount-transactions-value.myaccount-transaction-value-time' => $drawDate,
            'span.transactions-amount' => '€' . number_format($ticketData['amount_local'], 2),
            'span:nth-child(23)' => 'LPP' . $transactionData['token'],
            '#return-to-ticket-list-div' => 'Return to ticket list'
        ];

        foreach ($ticketDetailsCssAndText as $key => $value) {
            $this->elementHasText(
                $key,
                $value
            );
        }
    }

    /** @test */
    public function ticketDetails_haveNumbersPerLine(): void
    {
        $this->userService->updateUserTimezone(self::TEST_USER_EMAIL, self::EUROJACKPOT_TIMEZONE);
        $this->loginService->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->ticketService->buyOneLineQuickPickTicket(self::EUROJACKPOT_PLAY_URL);
        $this->driver->get($this->appUrl() . 'account/tickets/awaiting/');
        $this->globalService->clickElement('a.tooltip.tooltip-bottom > span');

        $numbers = [];
        $bNumbers = [];
        for ($i=1; $i<=7; $i++) {
            if ($i<6 ) {
                array_push($numbers,$this->globalService->getTextByCssSelector("div.tickets-lines > div > div:nth-child($i)"));
            } else {
                array_push($bNumbers, $this->globalService->getTextByCssSelector("div.tickets-lines > div > div:nth-child($i)"));
            }
        }

        $ticketLine = WhitelabelUserTicketLine::find('last');
        $this->assertSame($ticketLine->numbers, implode(',', $numbers));
        $this->assertSame($ticketLine->bnumbers, implode(',', $bNumbers));
    }
}
