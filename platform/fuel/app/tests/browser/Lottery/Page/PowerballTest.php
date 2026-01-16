<?php

namespace Tests\Browser\Lottery\Page;

use Test\Selenium\Interfaces\PowerballInterface;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;

final class PowerballTest extends AbstractSeleniumPageBase implements PowerballInterface
{

    private function getDomain(string $domain): bool
    {
        $url = $this->driver->getCurrentURL();
        return str_contains($url, $domain);
    }

    /** @test */
    public function playPage_ShowElements(): void
    {
        $this->driver->get(self::PLAY_URL);

        //Button Quick Pick
        $this->elementIsDisplayed(self::LOTTOPARK_PLAYPAGE_QUICKPICK);
        $this->elementIsEnabled(self::LOTTOPARK_PLAYPAGE_QUICKPICK);
        if ($this->getDomain($this->appUrl())) {
            $this->checkBackgroundColor(self::LOTTOPARK_ACTIVE_COLOUR, self::LOTTOPARK_PLAYPAGE_QUICKPICK);
        }

        $this->elementIsDisplayed(self::PLAY_CONTINUE);
        $this->elementIsNotEnabled(self::PLAY_CONTINUE);
        $this->elementIsDisplayed(self::LOTTOPARK_PLAYPAGE_MORELINES);
        $this->elementIsEnabled(self::LOTTOPARK_PLAYPAGE_MORELINES);
        $this->elementIsDisplayed(self::LOTTOPARK_PLAYPAGE_LESSLINES);
        $this->elementIsNotEnabled(self::LOTTOPARK_PLAYPAGE_LESSLINES);
        $this->elementIsDisplayed(self::LOTTOPARK_PLAYPAGE_IMAGE);

        //Tickets
        $this->elementIsDisplayed('#widget-ticket-form > div');
        $this->elementIsNotEnabled(self::LOTTOPARK_PLAYPAGE_BIN);
        $this->elementIsDisplayed(self::LOTTOPARK_PLAYPAGE_BIN);

        //Timer
        $this->elementIsDisplayed('#widget-ticket-time-remain-mobile');

        //Title is displayed
        $this->elementIsDisplayed('#play-lottery');

        $this->elementIsDisplayed(self::LOTTOPARK_PLAYPAGE_JACKPOT);

        //Play Powerball navigation is green.
        if ($this->getDomain($this->appUrl())) {
            $this->checkBackgroundColor(self::LOTTOPARK_ACTIVE_COLOUR, 'body > div.content-area > div.main-width.relative > nav > ul > li.content-nav-active > a');
        }

        //Powerball Results, Information, News
        if ($this->getDomain($this->appUrl())) {
            for ($i = 2; $i < 4; $i++) {
                $this->checkBackgroundColor(self::LOTTOPARK_INACTIVE_COLOUR, "body > div.content-area > div.main-width.relative > nav > ul > li:nth-child($i) > a");
            }
        }
    }

    /** @test */
    public function resultsPage_ShowElements(): void
    {
        $this->driver->get(self::RESULTS_URL);
        $this->elementIsDisplayed(self::LOTTOPARK_RESULTPAGE_DATESELECTOR);

        //Date next
        $this->elementIsDisplayed('#results-short-next');

        //Date previous
        $this->elementIsDisplayed('#results-short-prev');
        $this->elementIsDisplayed(self::LOTTOPARK_RESULTPAGE_WINNINGSNUMBERS);
        $this->elementIsDisplayed(self::LOTTOPARK_RESULTPAGE_JACKPOT);

        //Title is displayed
        $this->elementIsDisplayed('#content-results-lottery > div > div.main-width.content-width > div > section > article > h1');

        //Column names in table
        $array = [1 => "Tier", 2 => "Match X + X", 3 => "Winners", 4 => "Payout per winner"];

        foreach ($array as $key => $value) {
            $this->elementHasText(
                "#content-results-lottery > div > div.main-width.content-width > div > div.content-box-main > div.results-detailed-content > table > thead > tr > th:nth-child($key)",
                $value
            );
        };

        //Total Sum Text in table
        $this->elementHasText(
            "#content-results-lottery > div > div.main-width.content-width > div > div.content-box-main > div.results-detailed-content > table > tfoot > tr > td.text-left",
            "Total Sum:"
        );

        //Results Powerball navigation is green.
        if ($this->getDomain($this->appUrl())) {
            $this->checkBackgroundColor(self::LOTTOPARK_ACTIVE_COLOUR, '#content-results-lottery > div > div:nth-child(1) > nav > ul > li.content-nav-active > a');
        }

        //Powerball Play, Information, News
        if ($this->getDomain($this->appUrl())) {
            foreach ([1, 3] as $i) {
                $this->checkBackgroundColor(self::LOTTOPARK_INACTIVE_COLOUR, "#content-results-lottery > div > div:nth-child(1) > nav > ul > li:nth-child($i) > a");
            }
        }
    }

    /** @test */
    public function informationPage_ShowElements(): void
    {
        $this->driver->get(self::INFORMATION_URL);

        //Column names in table
        $arrayInfo = [1 => "Country", 2 => "Schedule", 3 => "Guess Range"];

        foreach ($arrayInfo as $key => $value) {
            $this->elementHasText(
                "body > div.content-area > div.main-width.content-width > div > div.content-box-main > div.info-short-content > table > thead > tr > th:nth-child($key)",
                $value
            );
        };

        //Title is displayed
        $this->elementIsDisplayed('body > div.content-area > div.main-width.content-width > div > section > article > h1');
        $this->elementIsDisplayed(self::LOTTOPARK_INFORMATIONPAGE_LATESTRESULT);

        //Column names in table
        $array = [1 => "Tier", 2 => "Match X + X", 3 => "Prize", 4 => "Chance to win"];

        foreach ($array as $key => $value) {
            $this->elementHasText(
                "body > div.content-area > div.main-width.content-width > div > div.content-box-main > div.info-detailed-content > table > thead > tr > th:nth-child($key)",
                $value
            );
        };

        //Total Sum Text in table
        $this->elementHasText(
            "body > div.content-area > div.main-width.content-width > div > div.content-box-main > div.info-detailed-content > table > tfoot > tr > td:nth-child(1)",
            "Overall chances of winning any prize :"
        );

        //Information Powerball navigation is green.
        if ($this->getDomain($this->appUrl())) {
            $this->checkBackgroundColor(self::LOTTOPARK_ACTIVE_COLOUR, 'body > div.content-area > div:nth-child(1) > nav > ul > li.content-nav-active > a');
        }

        //Powerball Play, Results
        if ($this->getDomain($this->appUrl())) {
            foreach ([1, 2] as $i) {
                $this->checkBackgroundColor(self::LOTTOPARK_INACTIVE_COLOUR, "body > div.content-area > div:nth-child(1) > nav > ul > li:nth-child($i) > a");
            }
        }
    }
}
