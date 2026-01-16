<?php

namespace Test\Selenium\Abstracts;

use Test\Selenium;
use Model_Currency;
use Model_Whitelabel_User;
use Model_Whitelabel_User_Ticket;
use Facebook\WebDriver\WebDriverBy;
use Model_Whitelabel_User_Ticket_Line;
use Test\Selenium\SeleniumGlobalService;
use Test\Selenium\Login\SeleniumLoginService;
use Facebook\WebDriver\WebDriverExpectedCondition;

abstract class AbstractSeleniumBasePlay extends Selenium
{
    public const BASE_PLAY_URL = "/play/";

    protected $lotteryUrl;
    private $userBalanceBeforePlay;
    private SeleniumGlobalService $globalService;

    public function setUp(): void
    {
        parent::setUp();
        $this->lotteryUrl = $this->appUrl() . self::BASE_PLAY_URL . $this->lotterySlug();
        $this->globalService = new SeleniumGlobalService($this->driver);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    abstract protected function lotterySlug(): string;

    abstract protected function lotteryLinePrice(): float;

    /**
     * @throws Exception
     */
    protected function loginUserAndUpdateBalance(int $amountToAdd = 100): void
    {
        // Login as test user
        SeleniumLoginService::loginUser($this->driver);

        // Update user's balance
        Model_Whitelabel_User::update_balance([SeleniumLoginService::getTestUserModel()['id']], $amountToAdd);
    }

    /**
     * Accept basket order and move on
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    protected function confirmBasketOrder(): void
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::titleContains("Your Order"));
        $this->driver->executeScript('window.scrollTo(0, 500);'); // scroll down to prevent bug

        // Click 'pay now' button to confirm order
        $this->globalService->waitForElementToBeClickableAndClick("#paymentSubmit", $this->driver);
    }

    protected function getUserBalance(): float
    {
        return SeleniumLoginService::getTestUserModel()['balance'];
    }

    protected function assertUserBalanceWithFrontend(float $userFrontBalance): void
    {
        $this->assertEquals($this->getUserBalance(), $userFrontBalance);
    }

    protected function linePrice(): float
    {
        $currencyRate = Model_Currency::find_by(["code" => "EUR"])[0]["rate"];
        $linePrice = ($this->lotteryLinePrice() * $currencyRate);
        $linePrice = number_format($linePrice, 2);
        return $linePrice;
    }

    /** Assert balance properly changed after buying tickets */
    protected function assertUserBalanceAfterPlay(float $ticketPrice): void
    {
        // Get user balance after buy ticket/s - Database
        $balanceAfterPlay = $this->getUserBalance();

        // Format balance to match frontend
        $before = ($this->userBalanceBeforePlay - $ticketPrice);
        $before = number_format($before, 2, ".", ",");

        // Get user balance after buy ticket/s - Frontend
        $frontUserBalanceAfterPlay = $this->globalService->getUserBalance($this->driver);

        // Format balance to match frontend

        $balanceAfterPlay = number_format($balanceAfterPlay, 2, ".", ",");

        // Delete currency symbol
        $frontUserBalanceAfterPlay = substr($frontUserBalanceAfterPlay, 3);

        // Make assertions
        $this->assertEquals($before, $balanceAfterPlay);
        $this->assertEquals($balanceAfterPlay, $frontUserBalanceAfterPlay);
    }

    /** Get last database ticket data */
    protected function getDatabaseTicketData(int $ticketsCount): array
    {
        // Get bought ticket
        $numberOfTickets = Model_Whitelabel_User_Ticket::find([
            "where" => [
                "whitelabel_user_id" => $this->globalService->getTestUserModel()["id"]
            ],
            'order_by' => ['id' => 'desc'],
            'limit' => $ticketsCount
        ])[0]["id"];

        $offset = ($numberOfTickets - $ticketsCount);
        $ticketQuery = Model_Whitelabel_User_Ticket::find_all($ticketsCount, $offset);
        $data = [];
        foreach ($ticketQuery as $query) {
            $ticketData = [];
            $ticketData["id"] = $query["id"];
            $ticketData["token"] = $query['token'];

            // Get all lines of bought ticket
            $ticketLines = Model_Whitelabel_User_Ticket_Line::find([
                "where" => [
                    "whitelabel_user_ticket_id" => $query["id"]
                ],
                'limit' => 10
            ]);

            // Count how many lines ticket has
            $ticketData["lines_count"] = count($ticketLines);

            foreach ($ticketLines as $ticketLine) {
                $ticketData["numbers"][] = $ticketLine['numbers'];
            }

            $data["tickets"][] = $ticketData;
        }
        return $data;
    }

    /** Convert front numbers to same structured array as database */
    protected function convertFrontNumbersIntoArray(array $frontNumbersArray, int $linesCount, int $numbersCount): array
    {
        // Chunk numbers string into pieces by numbers coun
        $frontNumbersChunked = array_chunk($frontNumbersArray, $numbersCount);

        // Iterate through array and implode values by coma separator
        $frontNumbers = [];
        for ($i = 0; $i < $linesCount; $i++) {
            $frontNumbers[] = implode(",", $frontNumbersChunked[$i]);
        }

        return $frontNumbers;
    }

    /** Assert Ticket details data with database data */
    protected function assertTicketDetailsWithDatabase(string $ticketToken): void
    {
        // Go to keno "Ticket Details"
        $this->driver->get($this->appUrl() . "account/tickets/details/{$ticketToken}");

        // Get multiplier value from ticket details page
        $frontMultiplerValueSelector = $this->driver->findElement(
            WebDriverBy::cssSelector(
                ".myaccount-transactions > div:nth-child(2) > span:nth-child(14)"
            )
        )->getText();

        $frontMultiplerValue = substr($frontMultiplerValueSelector, 1);

        $frontAmountSelector = $this->driver->findElement(
            WebDriverBy::cssSelector(
                ".myaccount-transactions > div:nth-child(2) > span:nth-child(20) > span"
            )
        )->getText();

        $frontAmountValue = substr($frontAmountSelector, 3);
        $frontLinesSelector = $this->driver->findElement(
            WebDriverBy::className(
                "tickets-lines-counter"
            )
        )->getText();

        $frontLinesValue = substr($frontLinesSelector, 7);
        $frontNumbersArray = $this->globalService->convertElementsTextValuesToArray(
            ".ticket-line-number",
            $this->driver
        );

        $frontNumbers = $this->convertFrontNumbersIntoArray($frontNumbersArray, $frontLinesValue, 10);
        $databaseTicketData = $this->getDatabaseTicketData(1);
        $databaseTicketLines = $databaseTicketData['lines_count'];

        # Assert ticket data (database with frontend)
        // Lines count
        $this->assertEquals($databaseTicketLines, $frontLinesValue);

        // Amount (price) // TODO: * multiplier (or just get price)
        $this->assertEquals(($this->linePrice() * $databaseTicketLines), $frontAmountValue);

        // Multiplier //TODO: get muiltipler database value
        $this->assertEquals(1, $frontMultiplerValue);

        // Numbers
        for ($i = 0; $i < $databaseTicketLines; $i++) {
            $this->assertEquals($databaseTicketData['numbers'][$i], $frontNumbers[$i]);
        }
    }
}
