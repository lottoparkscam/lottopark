<?php

namespace Test\Selenium\Lottery\Settings;

use Facebook\WebDriver\WebDriverBy;
use Test\Selenium\Abstracts\AbstractSelenium;
use Test\Selenium\Login\SeleniumLoginService;

class SeleniumLotterySettingsHelper extends AbstractSelenium
{
    public function getRandomEdit(): string
    {
        $this->getListPage();

        $editButtonSelector = 'body > div > div.col-md-10 > div > div > table > tbody > tr > td:nth-child(8) > a';
        $editButtons = $this->driver->findElements(WebDriverBy::cssSelector($editButtonSelector));

        $randKey = array_rand($editButtons, 1);
        $row = $randKey + 1;

        $lotteryRowSelector = "body > div > div.col-md-10 > div > div.table-responsive > table > tbody > tr:nth-child({$row}) > td:nth-child(1)";
        $lotteryName = $this->driver->findElement(WebDriverBy::cssSelector($lotteryRowSelector))->getText();

        $editButtons[$randKey]->click();

        return $lotteryName;
    }

    public function getEditByLotteryName(string $lotteryName): bool
    {
        $this->getListPage();
        $row = $this->getLotteryRowByName($lotteryName);

        if (!$row) {
            return false;
        }

        $editButtonSelector = "body > div > div.col-md-10 > div > div > table > tbody > tr:nth-child({$row}) > td:nth-child(8) > a";
        $this->driver->findElement(WebDriverBy::cssSelector($editButtonSelector))->click();
        return true;
    }

    public function getListPage()
    {
        $this->driver->get(SeleniumLoginService::MANAGER_LOGIN_URL . '/lotterysettings');
    }

    /**
     * @param string $lotteryName
     * @return bool|int
     */
    public function getQuickPickLinesByLotteryName(string $lotteryName)
    {
        $lotteryRow = $this->getLotteryRowByName($lotteryName);

        if (!$lotteryRow) {
            return false;
        }

        $quickPickSelector = "body > div > div.col-md-10 > div > div.table-responsive > table > tbody > tr:nth-child({$lotteryRow}) > td:nth-child(7)";
        $lotteryQuickPick = $this->driver->findElement(WebDriverBy::cssSelector($quickPickSelector))->getText();

        return intval($lotteryQuickPick);
    }

    /**
     * @return bool|int
     */
    public function getMinLinesByLotteryName(string $lotteryName)
    {
        $lotteryRow = $this->getLotteryRowByName($lotteryName);

        if (!$lotteryRow) {
            return false;
        }

        $minLineSelector = "body > div > div.col-md-10 > div > div.table-responsive > table > tbody > tr:nth-child({$lotteryRow}) > td:nth-child(6)";
        $lotteryMinLine = $this->driver->findElement(WebDriverBy::cssSelector($minLineSelector))->getText();

        return intval($lotteryMinLine);
    }

    /**
     * @return int|null|string
     */
    private function getLotteryRowByName(string $lotteryName)
    {
        $this->getListPage();

        $lotteryRowSelector = 'body > div > div.col-md-10 > div > div.table-responsive > table > tbody > tr > td:nth-child(1)';
        $lotteryRows = $this->driver->findElements(WebDriverBy::cssSelector($lotteryRowSelector));
        $foundLotteryKey = null;

        foreach ($lotteryRows as $key => $lotteryRow) {

            if ($lotteryRow->getText() === $lotteryName) {
                $foundLotteryKey = $key;
                break;
            }
        }

        return $foundLotteryKey + 1;
    }
}
