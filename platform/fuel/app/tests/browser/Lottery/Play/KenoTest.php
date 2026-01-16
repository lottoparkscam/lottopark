<?php

namespace Tests\Browser\Lottery\Play;

use Helpers_Lottery;
use Facebook\WebDriver\WebDriverBy;
use Test\Selenium\SeleniumGlobalService;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\Abstracts\AbstractSeleniumBasePlay;

final class KenoTest extends AbstractSeleniumBasePlay
{
    const MAX_LINES = 7;
    const MAX_NUMBERS = 10;

    private SeleniumGlobalService $globalService;

    public function setUp(): void
    {
        parent::setUp();
        $this->globalService = new SeleniumGlobalService($this->driver);
    }

    protected function lotterySlug(): string
    {
        return Helpers_Lottery::SLUGS[Helpers_Lottery::KENO_ID];
    }

    protected function lotteryLinePrice(): float
    {
        return 2; // USD
    }

    private function selectNumbersAndStake(int $numbersCount = 10, int $stake = 1): void
    {
        // Select 'How many numbers would you like to play?'
        $this->driver->findElement(
            WebDriverBy::cssSelector(
                "#widget-ticket-slip-size-select > option:nth-child({$numbersCount})"
            )
        )->click();

        // Select desired stake
        $this->driver->findElement(
            WebDriverBy::cssSelector(
                "#widget-ticket-stake-select > option:nth-child({$stake})"
            )
        )->click();
    }

    private function getFrontLineNumbersElements(): array
    {
        // Get all number fields elements
        $ticketNumbersElements = $this->driver->findElements(WebDriverBy::className("widget-ticket-number-value"));

        $this->driver->findElements(WebDriverBy::className("widget-ticket-number-value"));

        $chosenNumbers = [];
        foreach ($ticketNumbersElements as $ticketNumberElement) {
            $ticketNumberElementValue = $ticketNumberElement->getText();
            if ($ticketNumberElementValue != "") {
                $chosenNumbers[] = $ticketNumberElementValue;
            }
        }

        return $chosenNumbers;
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    private function pickNumbersByPicker($numbersToPick = []): void
    {
        // Go to Play page
        $this->driver->get($this->lotteryUrl);

        // Open the picker (modal)
        $this->driver->findElement(WebDriverBy::cssSelector(".widget-ticket-numbers > a:nth-child(1)"))->click();

        // Wait for modal
        $this->driver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('number-selector')
            )
        );

        // Iterate through available numbers and pick them
        foreach ($numbersToPick as $numberToPick) {
            // Grab number element
            $el = $this->driver->findElement(
                WebDriverBy::cssSelector(
                    "#number-selector > div.dialog-content > div > div:nth-child({$numberToPick}) > a"
                )
            );

            // Click it to pick number
            $el->click();
        }
    }

    /**
     * Buy keno ticket with quick pick option
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    private function buyKenoTicket(int $linesCount = 1, int $numbersCount = 10, int $multiplier = 1): array
    {
        // Go to Play page
        $this->driver->get($this->lotteryUrl);

        $this->selectNumbersAndStake($numbersCount, $multiplier);

        // Count how many lines are now
        $lines = $this->globalService->countElements(".widget-ticket-entity", $this->driver);

        if ($linesCount > 5) {
            while ($lines < $linesCount) {
                // Add more lines to match linesCount
                $addAnotherLineBtn = $this->driver->findElement(
                    WebDriverBy::className("widget-ticket-button-more-horizontal")
                );
                $addAnotherLineBtn->click();

                // Count how many lines are now
                $lines = $this->globalService->countElements(".widget-ticket-entity", $this->driver);
            }
        }

        // Get 'Quick Pick' button elements, iterate through and click them
        $quickPickButtons = $this->driver->findElements(
            WebDriverBy::className("widget-ticket-button-quickpick-horizontal")
        );

        for ($i = 0; $i < $linesCount; $i++) {
            $quickPickButtons[$i]->click();
        }

        $ticket_data = [];
        $ticket_data["line_data"]["numbers"] = $this->getFrontLineNumbersElements();
        $ticket_data["line_data"]["multiplier"][] = $multiplier;

        $this->driver->executeScript('window.scrollTo(0, 1000);'); // scroll down to see button

        // Wait for 'continue' button to be clickable and click it
        $this->globalService->waitForElementToBeClickableAndClick("#play-continue", $this->driver);

        $this->confirmBasketOrder();

        // Convert data to same structure as database
        $data[] = $this->convertFrontNumbersIntoArray(
            $ticket_data["line_data"]["numbers"],
            $linesCount,
            $numbersCount
        );

        return $data;
    }

    /**
     * Assert play page test
     * @test
     */
    public function playOneLineMaxNumbersQuickPick(): void
    {
        // Buy one line 10 numbers ticket
        $frontBoughtTicketsData = $this->buyKenoTicket(1, 10, 1);

        // Get database ticket data
        $databaseTicketData = $this->getDatabaseTicketData(1);

        // Iterate and assert that database values are equals to frontend values
        for ($i = 0; $i < count($databaseTicketData["tickets"]); $i++) {
            $this->assertEquals($databaseTicketData["tickets"][$i]["numbers"], $frontBoughtTicketsData[$i]);
        }

        // Make assertions
        $this->assertUserBalanceAfterPlay($this->linePrice());
    }

    /** @test */
    public function playOneLineMaxNumbersPicker(): void
    {
        // Pick 10 numbers with picker
        $numbersToPick = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        $this->pickNumbersByPicker($numbersToPick);

        // Get front numbers and convert them to same structure as database
        $frontNumbers = $this->getFrontLineNumbersElements();
        $frontNumbers = $this->convertFrontNumbersIntoArray(
            $frontNumbers,
            1,
            10
        );

        $this->driver->executeScript('window.scrollTo(0, 500);'); // scroll down to see button

        // Wait for 'continue' button to be clickable and click it
        $this->globalService->waitForElementToBeClickableAndClick("#play-continue", $this->driver);

        $this->confirmBasketOrder();

        // Get database ticket data
        $databaseTicketData = $this->getDatabaseTicketData(1);

        // Assert that database values are equals to frontend values
        $this->assertEquals($databaseTicketData["tickets"][0]["numbers"], $frontNumbers);

        // Make balance assertion
        $this->assertUserBalanceAfterPlay($this->linePrice());
    }

    /** @test */
    public function playMaxLinesMaxNumbers(): void
    {
        // Buy tickets with max lines and max numbers count
        $frontBoughtTicketsData = $this->buyKenoTicket(self::MAX_LINES, self::MAX_NUMBERS, 1);

        // Get database ticket data
        $databaseTicketData = $this->getDatabaseTicketData(1);

        // Iterate and assert that database values are equals to frontend values
        for ($i = 0; $i < count($databaseTicketData["tickets"]); $i++) {
            $this->assertEquals($databaseTicketData["tickets"][$i]["numbers"], $frontBoughtTicketsData[$i]);
        }

        // Make balance assertion
        $this->assertUserBalanceAfterPlay($this->linePrice() * self::MAX_LINES);
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    private function buyKenoTicketsEveryMultiplier(): array
    {
        $data = [];
        for ($i = 0; $i < self::MAX_LINES; $i++) { // TODO: Get multiplier count
            $data[] = $kenoTicketData = $this->buyKenoTicket(1, self::MAX_NUMBERS, $i + 1);
        }

        return $data;
    }

    /** @test */
    public function playOneLineMaxNumbersEveryMultiplier(): void
    {
        // Buy tickets with every multplier and get data
        $frontBoughtTicketsData = $this->buyKenoTicketsEveryMultiplier();

        // Get database ticket data
        $databaseTicketData = $this->getDatabaseTicketData(self::MAX_LINES);

        // Iterate and assert that database values are equals to frontend values
        for ($i = 0; $i < count($databaseTicketData["tickets"]); $i++) {
            $this->assertEquals($databaseTicketData["tickets"][$i]["numbers"], $frontBoughtTicketsData[$i][0]);
        }

        // Calculate total cost of every multiplier on line
        $balanceAfterPlay = 0;
        for ($i = 0; $i < self::MAX_LINES; $i++) {
            $balanceAfterPlay += ($this->linePrice() * ($i + 1));
        }

        // Assert user balance after play
        $this->assertUserBalanceAfterPlay($balanceAfterPlay);
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    private function buyKenoTicketsEveryNumbers(): array
    {
        $data = [];
        for ($i = 0; $i < self::MAX_LINES; $i++) {
            $data[] = $this->buyKenoTicket(1, $i + 1, 1);
        }

        return $data;
    }

    /** @test */
    public function playOneLineEveryNumbers(): void
    {
        // Buy tickets with every multplier and get data
        $frontBoughtTicketsData = $this->buyKenoTicketsEveryNumbers();

        // Get database ticket data
        $databaseTicketData = $this->getDatabaseTicketData(self::MAX_LINES);

        // Iterate and assert that database values are equals to frontend values
        for ($i = 0; $i < count($databaseTicketData["tickets"]); $i++) {
            $this->assertEquals($databaseTicketData["tickets"][$i]["numbers"], $frontBoughtTicketsData[$i][0]);
        }

        // Make balance assertion
        $this->assertUserBalanceAfterPlay($this->linePrice() * self::MAX_LINES);
    }
}
