<?php

namespace Tests\Browser\Manager;

use Model_Lottery;
use Model_Lottery_Provider;
use Model_Whitelabel_Lottery;
use Facebook\WebDriver\WebDriverBy;
use Forms_Whitelabel_Lottery_Settings_Edit;
use Test\Selenium\Login\SeleniumLoginService;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;
use Test\Selenium\Lottery\Settings\SeleniumLotterySettingsHelper;

final class LotterySettingsTest extends AbstractSeleniumPageBase
{
    private SeleniumLotterySettingsHelper $lotterySettingsHelper;
    private SeleniumLoginService $loginService;

    public function setUp(): void
    {
        parent::setUp();
        $this->lotterySettingsHelper = new SeleniumLotterySettingsHelper($this->driver);
        $this->loginService = new SeleniumLoginService($this->driver);
    }

    /** @test */
    public function changeMinimumLines_To3Lines_Success()
    {
        $this->loginService->loginManagerLottopark();
        $lotteryName = $this->lotterySettingsHelper->getRandomEdit();

        /** @var Model_Lottery $lottery */
        $lottery = Model_Lottery::find_one_by('name', $lotteryName);

        /** @var Model_Whitelabel_Lottery $whitelabelLottery */
        $whitelabelLottery = Model_Whitelabel_Lottery::find_one_by('lottery_id', $lottery['id']);

        /** @var Model_Lottery_Provider $lotteryProvider */
        $lotteryProvider = Model_Lottery_Provider::find_by_pk($whitelabelLottery['lottery_provider_id']);

        $minBets = intval($lotteryProvider['min_bets']);
        $maxBets = Forms_Whitelabel_Lottery_Settings_Edit::MAX_QUICK_PICK_VALUE;
        $randMinLine = rand($minBets, $maxBets);

        // min line from minBets to maxBets
        // quick-lin min min_lines max maxBets and mod of multiplier

        $submitButtonSelector = 'body > div > div.col-md-10 > div > form > div:nth-child(1) > button';
        $multiplier = (int)$lotteryProvider['multiplier'];

        // check critical points
        // first element is value, second element is assertion
        $minLinesToCheck = [
            [$randMinLine, true],
            [$maxBets, true],
            [$maxBets + 1, false],
            [$minBets, true],
            [$minBets - 1, false]
        ];

        foreach ($minLinesToCheck as list($minLinesValue, $assertPass)) {

            $previousMinLinesValue = $this->lotterySettingsHelper->getMinLinesByLotteryName($lotteryName);

            // go to edit page
            $this->lotterySettingsHelper->getEditByLotteryName($lotteryName);

            // set min lines into input
            $this->driver->findElement(WebDriverBy::id('inputMinLines'))->clear()->sendKeys($minLinesValue);

            // quick-pick must be at least min_lines
            $quickPickValue = $minLinesValue;

            if ($multiplier > 0) {
                $isQuickPickNotDividedByMultiplier = $quickPickValue % $multiplier !== 0;

                // quick-pick must be modulo of multiplier
                while ($isQuickPickNotDividedByMultiplier && $quickPickValue <= $maxBets) {
                    ++$quickPickValue;
                }

                if ($isQuickPickNotDividedByMultiplier) {
                    continue;
                }
            }

            // set quick_pick_lines the same or bigger than minimum line to pass test
            $this->driver->findElement(WebDriverBy::id('inputQuickPickLines'))->clear()->sendKeys($quickPickValue);

            // submit form
            $this->driver->findElement(WebDriverBy::cssSelector($submitButtonSelector))->click();
            $this->lotterySettingsHelper->getListPage();

            // find minLine from main list
            $minLine = $this->lotterySettingsHelper->getMinLinesByLotteryName($lotteryName);
            $assertValue = $assertPass ? $minLinesValue : $previousMinLinesValue;

            $this->assertSame($minLine, $assertValue);
        }
    }

    /** @test */
    public function changeQuickPick_To3Lines_Success()
    {
        $this->loginService->loginManagerLottopark();
        $lotteryName = $this->lotterySettingsHelper->getRandomEdit();

        /** @var Model_Lottery $lottery */
        $lottery = Model_Lottery::find_one_by('name', $lotteryName);

        /** @var Model_Whitelabel_Lottery $whitelabelLottery */
        $whitelabelLottery = Model_Whitelabel_Lottery::find_one_by('lottery_id', $lottery['id']);

        /** @var Model_Lottery_Provider $lotteryProvider */
        $lotteryProvider = Model_Lottery_Provider::find_by_pk($whitelabelLottery['lottery_provider_id']);
        $multiplier = (int)$lotteryProvider['multiplier'];

        $minBets = intval($lotteryProvider['min_bets']);
        $maxBets = Forms_Whitelabel_Lottery_Settings_Edit::MAX_QUICK_PICK_VALUE;

        $submitButtonSelector = 'body > div > div.col-md-10 > div > form > div:nth-child(1) > button';
        $randMinLine = rand($minBets, $maxBets);

        // check critical points
        // first element is value, second is assertion
        $quickLinesToCheck = [
            [$randMinLine - 1, false],
            [$randMinLine, $multiplier == 0 || $randMinLine % $multiplier === 0],
            [$maxBets, $multiplier == 0 || $maxBets % $multiplier === 0],
            [$maxBets + 1, false]
        ];

        foreach ($quickLinesToCheck as list($quickLineValue, $assertPass)) {

            $previousQuickLineValue = $this->lotterySettingsHelper->getQuickPickLinesByLotteryName($lotteryName);

            // go to edit page
            $this->lotterySettingsHelper->getEditByLotteryName($lotteryName);

            // set min lines into input
            $this->driver->findElement(WebDriverBy::id('inputMinLines'))->clear()->sendKeys($randMinLine);

            // set quick_pick_lines the same or bigger than minimum line to pass test
            $this->driver->findElement(WebDriverBy::id('inputQuickPickLines'))->clear()->sendKeys($quickLineValue);

            // submit form
            $this->driver->findElement(WebDriverBy::cssSelector($submitButtonSelector))->click();
            $this->lotterySettingsHelper->getListPage();

            // find minLine from main list
            $quickLine = $this->lotterySettingsHelper->getQuickPickLinesByLotteryName($lotteryName);
            $assertValue = $assertPass ? $quickLineValue : $previousQuickLineValue;
            $this->assertSame($quickLine, $assertValue);
        }
    }
}
