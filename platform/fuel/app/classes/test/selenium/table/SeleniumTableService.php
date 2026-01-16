<?php

namespace Test\Selenium\Table;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Test\Selenium\Abstracts\AbstractSelenium;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverExpectedCondition;

class SeleniumTableService extends AbstractSelenium
{
    private RemoteWebElement $table;
    private string $tableXpath;
    private bool $excludeFirstRow;
    private bool $excludeLastRow;
    private string $exampleDataToWaitFor;

    public function __construct(RemoteWebDriver $driver)
    {
        parent::__construct($driver);

        $this->excludeFirstRow = false;
        $this->excludeLastRow = false;
    }

    /**
     * @param string $xpath
     * @param string $exampleDataToWaitFor Something that exists in tbody after data fetch
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function setTable(string $xpath, string $exampleDataToWaitFor): void
    {
        $this->tableXpath = $xpath;

        $this->waitForData($exampleDataToWaitFor);

        $table = $this->driver->findElement(WebDriverBy::xpath($xpath));
        $this->table = $table;
        $this->exampleDataToWaitFor = $exampleDataToWaitFor;
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function reload(): void
    {
        $this->setTable($this->tableXpath, $this->exampleDataToWaitFor);
    }

    public function excludeFirstRow(): void
    {
        $this->excludeFirstRow = true;
    }

    public function excludeLastRow(): void
    {
        $this->excludeLastRow = true;
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function waitForData(string $xPath): void
    {
        $this->driver->wait(100, 1000)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::xpath($xPath)
            )
        );
    }

    /** Counts from 0 */
    public function findRowNumberByContent(string $columnName, string $content): ?int
    {
        $excluded = "";

        if ($this->excludeFirstRow) {
            $excluded .= ":not(:first-child)";
        }

        if ($this->excludeLastRow) {
            $excluded .= ":not(:last-child)";
        }

        $columnNumber = $this->findColumnNumberByContent($columnName) + 1;
        $rows = $this->table->findElements(WebDriverBy::cssSelector("tbody > tr{$excluded} > td:nth-child({$columnNumber})"));

        /** @var RemoteWebElement $column */
        foreach ($rows as $rowNumber => $row) {

            if ($row->getText() === $content) {

                return $this->excludeFirstRow ? ++$rowNumber : $rowNumber;
            }
        }

        return null;
    }

    public function findContentByRowNumber(string $columnName, int $rowNumber): ?string
    {
        ++$rowNumber;

        if ($this->excludeFirstRow) {
            ++$rowNumber;
        }

        $columnNumber = $this->findColumnNumberByContent($columnName) + 1;
        $row = $this->table->findElement(WebDriverBy::cssSelector("tbody > tr:nth-child({$rowNumber}) > td:nth-child({$columnNumber})"));

        if ($row instanceof WebDriverElement) {
            return $row->getText();
        }

        return null;
    }


    /** Counts from 0 */
    public function findColumnNumberByContent(string $columnName): ?int
    {
        $columns = $this->table->findElements(WebDriverBy::cssSelector('thead > tr:nth-child(1) > th'));

        /** @var RemoteWebElement $column */
        foreach ($columns as $key => $column) {

            if ($column->getText() === $columnName) {
                return $key;
            }
        }

        return null;
    }


    public function calcRows(): ?int
    {
        $rows = $this->table->findElements(WebDriverBy::cssSelector("tbody > tr"));
        $rowsAmount = count($rows);

        if ($this->excludeFirstRow) {
            --$rowsAmount;
        }

        if ($this->excludeLastRow) {
            --$rowsAmount;
        }

        return $rowsAmount;
    }
}
