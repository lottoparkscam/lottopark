<?php

namespace Tests\Browser\Lottery\Play;

use Model_Whitelabel_User;
use Facebook\WebDriver\WebDriverBy;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;
use Test\Selenium\Login\SeleniumLoginService;
use Facebook\WebDriver\WebDriverExpectedCondition;

final class RafflePlayTest extends AbstractSeleniumPageBase
{
    const PLAY_RAFFLE_URL = "play-raffle/gg-world-raffle";
    const RESULTS_RAFFLE_URL = "results-raffle/gg-world-raffle";
    const INFORMATION_RAFFLE_URL = "information-raffle/gg-world-raffle";

    const LINE_PRICE = 8.42; // EUR
    private SeleniumLoginService $loginService;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginService = new SeleniumLoginService($this->driver);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    private function loginUserAndUpdateBalance($amountToAdd = 1000): void
    {
        // Login as test user
        $this->loginService->loginUser();

        // Update user's balance
        Model_Whitelabel_User::update_balance([$this->loginHelper->getTestUserModel()['id']], $amountToAdd);
    }

    /** @test */
    public function playPageReachable(): void
    {
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);
        $this->driver->findElement(WebDriverBy::id('play-lottery'));
        $this->assertStringContainsString("GG World Raffle", $this->driver->getTitle());
    }

    /** @test */
    public function resultsPageReachable(): void
    {
        $this->driver->get($this->appUrl() . self::RESULTS_RAFFLE_URL);
        $this->driver->findElement(WebDriverBy::className('results-detailed-content'));
        $this->assertStringContainsString(
            "Results GG World Raffle",
            $this->driver->findElement(WebDriverBy::cssSelector('article > h1'))->getText()
        );
    }

    /** @test */
    public function informationPageReachable(): void
    {
        $this->driver->get($this->appUrl() . self::INFORMATION_RAFFLE_URL);
        $this->driver->findElement(WebDriverBy::className('info-short-content'));
        $this->assertStringContainsString(
            "GG World Raffle Information",
            $this->driver->findElement(WebDriverBy::cssSelector('article > h1'))->getText()
        );
    }

    /** @test */
    public function newsPageReachable(): void
    {
        $this->driver->get($this->appUrl() . "category/gg-world-raffle/");
        $this->driver->findElement(WebDriverBy::id('newsCategorySelect'));
        $this->assertStringContainsString(
            "News – GGWordlRaffle",
            $this->driver->findElement(WebDriverBy::className('news-title'))->getText()
        );
    }

    //Assert that displayed tickets are equals
    private function assertTickets(array $tickets, string $selectorClassname): void
    {
        $selectorTickets = $this->driver->findElements(
            WebDriverBy::className($selectorClassname)
        );

        // Convert elements and sort them to match numbers
        $selectorTickets = $this->convertElementsTextToArray($selectorTickets);
        sort($selectorTickets);

        for ($i = 0; $i < count($tickets); $i++) {
            $this->assertEquals($selectorTickets[$i], $tickets[$i]);
        }
    }

    private function convertElementsTextToArray(array $elements): array
    {
        $textArray = [];
        foreach ($elements as $element) {
            array_push($textArray, $element->getText());
        }
        return $textArray;
    }

    //Pick first available tickets (count by $ticketsCount)
    private function pickTickets(int $ticketsCount = 10): void
    {
        $iterator = $ticketsCount;
        for ($i = 1; $i <= $iterator; $i++) {
            if ($this->driver->findElement(
                WebDriverBy::cssSelector("*[data-number='{$i}']")
            )->getCSSValue('opacity') > 0.3) {
                $this->driver->findElement(WebDriverBy::cssSelector("*[data-number='{$i}']"))->click();
            } else {
                $iterator += 1;
                continue;
            }
        }
    }

    /** @test */
    public function playRaffle_Success(): void
    {
        $this->loginUserAndUpdateBalance(1000);

        $ticketsToPickCount = 25;

        $ticketsPrice = ($ticketsToPickCount * self::LINE_PRICE);

        // Go to play page
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);

        // Find User's balance
        $balanceBeforePlay = $this->loginService::getTestUserModel()['balance'];

        // Get available tickets before buy tickets
        $availableNumbersBeforePick = $this->driver->findElement(
            WebDriverBy::cssSelector('.widget-ticket-entity.widget-raffle-ticket-summary > strong:nth-child(1)')
        )->getText();

        // Get already taken tickets count
        $alreadyTakenNumbersCount = count($this->driver->findElements(WebDriverBy::className("raffle-taken-number")));

        // Assert that available tickets match ones from widget
        $this->assertEquals($availableNumbersBeforePick, (1000 - $alreadyTakenNumbersCount));

        // Raffle tickets, €8.42 each
        $this->pickTickets($ticketsToPickCount);

        // Get 'Single ticket price' value and assert it
        $oneNumberPriceValue = $this->driver->findElement(WebDriverBy::className('raffle-line-price'))->getText();
        $this->assertEquals("€8.42", $oneNumberPriceValue);

        // Get 'Your tickets' widget value and assert if that equals number of picked tickets
        $yourTicketsValue =
            $this->driver->findElement(WebDriverBy::cssSelector(".widget-raffle-ticket-summary > span > span"))->getText();

        $this->assertEquals($ticketsToPickCount, $yourTicketsValue);

        // Get "Summary" value and check if equals price of picked tickets
        $totalPriceValue = $this->driver->findElement(WebDriverBy::className("raffle-total-value"))->getText();
        $this->assertEquals("€" . number_format($ticketsPrice, 2), $totalPriceValue);

        // Wait till all tickets are drawn
        $this->driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className("raffle-order-ticket"))
        );

        // Get picked tickets and check if they are same as displayed in widget area
        $pickedNumbersElements = $this->driver->findElements(WebDriverBy::className("raffle-picked-number"));

        // Convert array of elements to array of text values
        $pickedTickets = $this->convertElementsTextToArray($pickedNumbersElements);

        $this->assertTickets($pickedTickets, "widget-chosen-ticket");

        // Submit to buy tickets
        $this->driver->findElement(WebDriverBy::className("raffle-order-ticket"))->click();

        // Wait until summary page load
        $this->driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::className("ticket-card-details")))
        );

        // Assert tickets displayed on 'thank you page' are same as on 'play page'
        $this->assertTickets($pickedTickets, "raffle-number");

        // Get summary price value and assert to match with picked tickets price
        $summaryPrice = $this->driver->findElement(WebDriverBy::className("ticket-card-amount"))->getText();
        $this->assertEquals("€" . number_format($ticketsPrice, 2), $summaryPrice);

        // Get user balance after play
        $balanceAfterPlay = $this->loginService::getTestUserModel()['balance'];

        // Assert that User's balance changed by ticket's price
        $this->assertEquals($balanceAfterPlay, ($balanceBeforePlay - $ticketsPrice));

        // Go to play page
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);

        // Get available tickets after buy tickets
        $availableTicketsAfterPick = $this->driver->findElement(
            WebDriverBy::cssSelector('.widget-ticket-entity.widget-raffle-ticket-summary > strong:nth-child(1)')
        )->getText();

        // Assert that available tickets value changed by number of tickets we bought
        $this->assertEquals(($availableNumbersBeforePick - $ticketsToPickCount), $availableTicketsAfterPick);
    }

    /** @test */
    public function playLottery_NotEnoughBalance_Failure(): void
    {
        // Login as test user
        $this->loginService->loginUser();

        // Set user's balance to 0
        $user = $this->loginService::getTestUserModel();
        $user->set(['balance' => 0]);
        $user->save();

        // Find User's balance
        $balanceBeforePlay = $this->loginService->getTestUserModel()['balance'];

        // Go to play page
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);

        // Get available tickets before buy tickets
        $availableNumbersBeforePick = $this->driver->findElement(
            WebDriverBy::cssSelector('.widget-ticket-entity.widget-raffle-ticket-summary > strong:nth-child(1)')
        )->getText();

        // Pick one ticket
        $this->pickTickets(1);

        // Wait till all lines are drawn
        $this->driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className("raffle-order-ticket"))
        );

        // Submit to buy tickets
        $this->driver->findElement(WebDriverBy::className("raffle-order-ticket"))->click();

        // Wait until error is visible
        $this->driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::className("platform-alert")))
        );

        // Get user balance after play
        $balanceAfterPlay = $this->loginService->getTestUserModel()['balance'];

        // Assert that User's balance not changed
        $this->assertEquals($balanceAfterPlay, $balanceBeforePlay);

        // Go to play page
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);

        // Get available tickets after buy tickets
        $availableNumbersAfterPick = $this->driver->findElement(
            WebDriverBy::cssSelector('.widget-ticket-entity.widget-raffle-ticket-summary > strong:nth-child(1)')
        )->getText();

        // Assert that available tickets value changed by tickets we bought
        $this->assertEquals($availableNumbersBeforePick, $availableNumbersAfterPick);
    }

    /**
     * Try to buy 1k tickets once
     * @test
     * */
    public function playLotteryMaxTickets_Success(): void
    {
        $this->markTestIncomplete(); // Buying 1k not working right now

        $this->loginUserAndUpdateBalance(8500);

        $ticketsToPickCount = 1000;

        $ticketsPrice = ($ticketsToPickCount * self::LINE_PRICE);

        // Go to play page
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);

        // Find User's balance
        $balanceBeforePlay = $this->loginService::getTestUserModel()['balance'];

        // Get available tickets before buy tickets
        $availableNumbersBeforePick = $this->driver->findElement(
            WebDriverBy::cssSelector('.widget-ticket-entity.widget-raffle-ticket-summary > strong:nth-child(1)')
        )->getText();

        // Get already taken tickets count
        $alreadyTakenNumbersCount = count($this->driver->findElements(WebDriverBy::className("raffle-taken-number")));

        // Assert that available tickets match ones from widget
        $this->assertEquals($availableNumbersBeforePick, (1000 - $alreadyTakenNumbersCount));

        // Raffle tickets, €8.42 each
        $this->pickTickets($ticketsToPickCount);

        // Get 'Your tickets' widget value and assert if that equals number of picked tickets
        $yourTicketsValue =
            $this->driver->findElement(WebDriverBy::cssSelector(".widget-raffle-ticket-summary > span > span"))->getText();

        $this->assertEquals($ticketsToPickCount, $yourTicketsValue);

        // Wait till all tickets are drawn
        $this->driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className("raffle-order-ticket"))
        );

        // Scroll to see 'buy tickets' button
        $this->driver->executeScript("window.scrollBy(0,13200)");

        // Submit to buy tickets
        $this->driver->findElement(WebDriverBy::className("raffle-order-ticket"))->click();

        // Wait until summary page load
        $this->driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::className("ticket-card-details")))
        );

        // Get summary price value and assert to match with picked tickets price
        $summaryPrice = $this->driver->findElement(WebDriverBy::className("ticket-card-amount"))->getText();
        $this->assertEquals("€" . number_format($ticketsPrice, 2), $summaryPrice);

        // Get user balance after play
        $balanceAfterPlay = $this->loginService::getTestUserModel()['balance'];

        // Assert that User's balance changed by ticket's price
        $this->assertEquals($balanceAfterPlay, ($balanceBeforePlay - $ticketsPrice));

        // Go to play page
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);

        // Get available tickets after buy tickets
        $availableTicketsAfterPick = $this->driver->findElement(
            WebDriverBy::cssSelector('.widget-ticket-entity.widget-raffle-ticket-summary > strong:nth-child(1)')
        )->getText();

        // Assert that available tickets value changed by number of tickets we bought
        $this->assertEquals(($availableNumbersBeforePick - $ticketsToPickCount), $availableTicketsAfterPick);
    }

    /**
     * Check if All/Even/Odds lines filters work properly
     * @test
     */
    public function showOnlyAvailableTickets_Filter(): void
    {
        $this->loginUserAndUpdateBalance(1000);

        // Go to play page
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);

        // Assert raffle play widget visibility
        $raffle_widget = $this->driver->findElement(WebDriverBy::className("widget-raffle-ticket-entity"))->isDisplayed();
        $this->assertTrue($raffle_widget);

        # Check for available tickets
        // Buy some random tickets
        for ($i = 0; $i < 10; $i++) {
            $this->driver->findElement(WebDriverBy::className("raffle-ticket-add-random"))->click();
        }

        // Submit to buy tickets
        $this->driver->findElement(WebDriverBy::className("raffle-order-ticket"))->click();

        // Go back to Play page
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);

        // Get all unavailable tickets count
        $unavailableTicketsCount = count($this->driver->findElements(
            WebDriverBy::cssSelector(".raffle-taken-number")
        ));

        // Get all unavailable even tickets count
        $this->driver->findElement(WebDriverBy::className("even-numbers"))->click();
        $unavailableEvenTicketsCount = count($this->driver->findElements(
            WebDriverBy::cssSelector(".raffle-taken-number:not([class*='hidden'])")
        ));

        // Get all unavailable odd tickets count
        $this->driver->findElement(WebDriverBy::className("odds-numbers"))->click();
        $unavailableOddTicketsCount = count($this->driver->findElements(
            WebDriverBy::cssSelector(".raffle-taken-number:not([class*='hidden'])")
        ));

        $this->driver->findElement(WebDriverBy::className("all-numbers"))->click();

        // Choose to display only available tickets
        $this->driver->findElement(WebDriverBy::id("js-availability-switcher-checkbox"))->click();

        // Get all displayed tickets count
        $displayedTicketsCount = count($this->driver->findElements(
            WebDriverBy::cssSelector(".raffle-number:not([class*='hidden'])")
        ));

        // Assert that unavailable tickets are not displayed
        $this->assertEquals($displayedTicketsCount, (1000 - $unavailableTicketsCount));

        // Filter to show only available even tickets
        $this->driver->findElement(WebDriverBy::className("even-numbers"))->click();

        // Get all shown tickets
        $displayedEvenTicketsCount = count($this->driver->findElements(
            WebDriverBy::cssSelector(".raffle-number:not([class*='hidden'])")
        ));

        // Assert that unavailable tickets are not displayed when only available + even are checked
        $this->assertEquals($displayedEvenTicketsCount, (500 - $unavailableEvenTicketsCount));

        // Filter to show only available odd tickets
        $this->driver->findElement(WebDriverBy::className("odds-numbers"))->click();

        // Get all shown tickets
        $displayedOddTicketsCount = count($this->driver->findElements(
            WebDriverBy::cssSelector(".raffle-number:not([class*='hidden'])")
        ));

        // Assert that unavailable tickets are not displayed when only available + odds are checked
        $this->assertEquals($displayedOddTicketsCount, (500 - $unavailableOddTicketsCount));
    }

    /**
     * Try to buy some lines with "I'm feeling lucky" button
     * @test
     */
    public function feelingLuckySuccess(): void
    {
        $this->loginUserAndUpdateBalance(1000);

        $ticketsToPickCount = 5;

        $ticketsPrice = ($ticketsToPickCount * self::LINE_PRICE);

        // Go to play page
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);

        // Find User's balance
        $balanceBeforePlay = $this->loginService::getTestUserModel()['balance'];

        // Get available tickets before buy tickets
        $availableTicketsBeforePick = $this->driver->findElement(
            WebDriverBy::cssSelector('.widget-ticket-entity.widget-raffle-ticket-summary > strong:nth-child(1)')
        )->getText();

        // Get already taken tickets count
        $alreadyTakenTicketsCount = count($this->driver->findElements(WebDriverBy::className("raffle-taken-number")));

        // Assert that available tickets match ones from widget
        $this->assertEquals($availableTicketsBeforePick, (1000 - $alreadyTakenTicketsCount));

        // Click "I'm feeling lucky" button
        for ($i = 0; $i < $ticketsToPickCount; $i++) {
            $this->driver->findElement(WebDriverBy::className("raffle-ticket-add-random"))->click();
        }

        // Get 'Your tickets' widget value and assert if that equals number of picked tickets
        $yourTicketsValue =
            $this->driver->findElement(WebDriverBy::cssSelector(".widget-raffle-ticket-summary > span > span"))->getText();

        $this->assertEquals($ticketsToPickCount, $yourTicketsValue);

        // Get "Summary" value and check if equals price of picked tickets
        $totalPriceValue = $this->driver->findElement(WebDriverBy::className("raffle-total-value"))->getText();
        $this->assertEquals("€" . number_format($ticketsPrice, 2), $totalPriceValue);

        // Wait till all tickets are drawn
        $this->driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className("raffle-order-ticket"))
        );

        // Get picked tickets
        $pickedNumbersElements = $this->driver->findElements(WebDriverBy::className("raffle-picked-number"));

        // Convert array of elements to array of text values
        $pickedTickets = $this->convertElementsTextToArray($pickedNumbersElements);

        // Submit to buy tickets
        $this->driver->findElement(WebDriverBy::className("raffle-order-ticket"))->click();

        // Wait until summary page load
        $this->driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::className("ticket-card-details")))
        );

        // Assert tickets displayed on 'thank you page' are same as on 'play page'
        $this->assertTickets($pickedTickets, "raffle-number");

        // Get summary price value and assert to match with picked tickets price
        $summaryPrice = $this->driver->findElement(WebDriverBy::className("ticket-card-amount"))->getText();

        // Assert that bought ticket price matches one from thank you page
        $this->assertEquals("€" . number_format($ticketsPrice, 2), $summaryPrice);

        // Get user balance after play
        $balanceAfterPlay = $this->loginService::getTestUserModel()['balance'];

        // Assert that User's balance changed by ticket's price
        $this->assertEquals($balanceAfterPlay, ($balanceBeforePlay - $ticketsPrice));

        // Go to play page
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);

        // Get available tickets after buy tickets
        $availableTicketsAfterPick = $this->driver->findElement(
            WebDriverBy::cssSelector('.widget-ticket-entity.widget-raffle-ticket-summary > strong:nth-child(1)')
        )->getText();

        // Assert that available tickets value changed by number of tickets we bought
        $this->assertEquals(($availableTicketsBeforePick - $ticketsToPickCount), $availableTicketsAfterPick);
    }

    /** @test */
    public function checkIfClearAllButton_Works(): void
    {
        $this->loginService->loginUser();

        // Go to play page
        $this->driver->get($this->appUrl() . self::PLAY_RAFFLE_URL);

        // Click "I'm feeling lucky" button
        for ($i = 0; $i < 5; $i++) {
            $this->driver->findElement(WebDriverBy::className("raffle-ticket-add-random"))->click();
        }

        // Get 'Your tickets' widget value and assert if that equals number of picked tickets
        $yourTicketsValueBeforeClearAll =
            $this->driver->findElement(WebDriverBy::cssSelector(".widget-raffle-ticket-summary > span > span"))->getText();

        $this->assertEquals(5, $yourTicketsValueBeforeClearAll);

        // Get "Summary" value and check if equals price of picked tickets
        $summaryValueBeforeClearAll = $this->driver->findElement(WebDriverBy::className("raffle-total-value"))->getText();

        $this->assertEquals("€42.10", $summaryValueBeforeClearAll);

        // Click 'clear all' button
        $this->driver->findElement(WebDriverBy::className("raffle-ticket-clear-all"))->click();

        // Check if 'clear all' button is disabled now
        $this->assertFalse($this->driver->findElement(WebDriverBy::className("raffle-ticket-clear-all"))->isEnabled());

        // Check if 'buy tickets' button is disabled now
        $this->assertFalse($this->driver->findElement(WebDriverBy::className("raffle-order-ticket"))->isEnabled());

        // Get 'Your tickets' widget value after clearing
        $yourTicketsValueAfterClearAll =
            $this->driver->findElement(WebDriverBy::cssSelector(".widget-raffle-ticket-summary > span > span"))->getText();

        // Get "Summary" value after clearing
        $summaryValueAfterClearAll = $this->driver->findElement(WebDriverBy::className("raffle-total-value"))->getText();

        // Assert that 'Your tickets' and 'Summary' are 0
        $this->assertEquals("€0.00", $summaryValueAfterClearAll);
        $this->assertEquals("0", $yourTicketsValueAfterClearAll);
    }
}
