<?php

namespace Test\Selenium\CRM\User;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\Abstract\AbstractSelenium;

class SeleniumCrmUserHelper extends AbstractSelenium
{
    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function addColumnToView(string $columnName)
    {
        $viewSelect = $this->driver->findElement(
            WebDriverBy::xpath(
                "//*[@id='main-wrapper']/div/div[2]/div/div[2]/div/div/div/div/label[contains(text(),'{$columnName}')]/input"
            )
        );

        if (!$viewSelect->isSelected()) {
            $viewSelect->click();
        }

        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::xpath(
                    "//*[@id='main-wrapper']/div/div[2]/div/div[2]/div/div/div/div/div[4]/div/table/thead/tr[1]/th[contains(text(),'{$columnName}')]"
                )
            )
        );
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function clickManualDeposit(int $row, int $column): void
    {
        // xpath number not starts from 0
        ++$row;
        ++$column;

        $manualDepositButtonElement = WebDriverBy::xpath(
            "//*[@id='main-wrapper']/div/div[2]/div/div[2]/div/div/div/div/div[4]/div/table/tbody/tr[{$row}]/td[{$column}]/div[2]/div[2]/a"
        );

        $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable($manualDepositButtonElement));
        $manualDepositButton = $this->driver->findElement($manualDepositButtonElement);
        $manualDepositButton->click();
    }
}
