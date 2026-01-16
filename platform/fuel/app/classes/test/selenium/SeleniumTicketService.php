<?php

namespace Test\Selenium;

use Container;
use Models\WhitelabelUserTicket;
use Facebook\WebDriver\WebDriverBy;
use Models\WhitelabelUserTicketLine;
use Test\Selenium\Abstracts\AbstractSelenium;
use Repositories\WhitelabelUserTicketRepository;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;

class SeleniumTicketService extends AbstractSelenium
{
    public function getTicketByLotteryId(string $lotteryId): WhitelabelUserTicket
    {
        $whitelabelUserTicketRepository = Container::get(WhitelabelUserTicketRepository::class);
        $whitelabelUserTicketRepository->pushCriterias([
            new Model_Orm_Criteria_Where('lottery_id', $lotteryId)
        ]);
        return $whitelabelUserTicketRepository->findOne();
    }

    public function buyQuickPickTicket(string $playUrl): void
    {
        $this->chooseTicket($playUrl);
        $this->driver->wait()->until(WebDriverExpectedCondition::titleContains("Your Order"));
        $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('paymentSubmit')))->click();
    }

    public function buyOneLineQuickPickTicket(string $playUrl): void
    {
        $this->driver->get($playUrl);
        $this->driver->executeScript("document.getElementsByClassName('widget-ticket-button-quickpick')[0].click();");
        $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id("play-continue")))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::titleContains("Your Order"));
        $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('paymentSubmit')))->click();
    }

    public function chooseTicket(string $playUrl): void
    {
        $this->driver->get($playUrl);
        $this->driver->findElement(WebDriverBy::className("widget-ticket-quickpick-all"))->click();
        $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id("play-continue")))->click();
    }
}
