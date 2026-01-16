<?php

namespace Tests\Unit\Classes\Services;

use Carbon\Carbon;
use Helpers_Time;
use Lotto_View;
use Models\Lottery;
use Repositories\LotteryRepository;
use Services\WidgetService;
use Test_Feature;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class WidgetServiceTest extends Test_Feature
{
    private WidgetService $widgetService;
    private Lottery $lottery;
    private LotteryRepository $lotteryRepository;
    protected $in_transaction = false;
    private array $oldDrawDates;
    private ?string $oldLastDateLocal;
    private string $oldLocale;

    public function setUp(): void
    {
        $this->oldLocale = setlocale(LC_ALL, 0);
        putenv('LANGUAGE=pl_PL.utf8');
        setlocale(LC_ALL, 'pl_PL.utf8');

        // Setup translations from wordpress's path
        bindtextdomain('lotto-platform', APPPATH . '../../../wordpress/wp-content/plugins/lotto-platform/languages/gettext');
        textdomain('lotto-platform');

        parent::setUp();
        $this->lotteryRepository = $this->container->get(LotteryRepository::class);
        $this->widgetService = $this->container->get(WidgetService::class);
        $this->lottery = $this->lotteryRepository->findOneBySlug('powerball');
        $this->oldDrawDates = $this->lottery->drawDates;
        $this->oldLastDateLocal = $this->lottery->lastDateLocal;
    }

    public function tearDown(): void
    {
        putenv('LANGUAGE');
        setlocale(LC_ALL, $this->oldLocale);
        parent::tearDown();
        $this->lottery->lastDateLocal = $this->oldLastDateLocal;
        $this->lottery->drawDates = $this->oldDrawDates;
        $this->lottery->save();
    }

    /** @test */
    public function getNextDrawFormattedForListWidget_daysFormat(): void
    {
        $now = Carbon::now($this->lottery->timezone);
        $this->lottery->lastDateLocal = $now->format(Helpers_Time::DATETIME_FORMAT);
        $this->lottery->nextDateLocal = null;
        $this->lottery->nextDateUtc = null;
        $this->lottery->drawDates = [
            $now->addDays(2)->format('D') . ' 22:59'
        ];
        $this->lottery->save();

        $countdown = Lotto_View::next_draw_countdown($this->lottery->to_array());
        $nextDrawFormatted = $this->widgetService->getNextDrawFormattedForListWidget($this->lottery->to_array());
        $this->assertSame("{$countdown->d} dni {$countdown->h} godz", $nextDrawFormatted);
    }

    /** @test */
    public function getNextDrawFormattedForListWidget_minutesFormat(): void
    {
        $now = Carbon::now($this->lottery->timezone);
        $this->lottery->lastDateLocal = $now->format(Helpers_Time::DATETIME_FORMAT);
        $this->lottery->nextDateLocal = null;
        $this->lottery->nextDateUtc = null;
        $this->lottery->drawDates = [
            $now->addMinutes(20)->format('D H:i')
        ];
        $this->lottery->save();

        $countdown = Lotto_View::next_draw_countdown($this->lottery->to_array());
        $nextDrawFormatted = $this->widgetService->getNextDrawFormattedForListWidget($this->lottery->to_array());
        $expected = "    <span class=\"countdown-item\">
        {$countdown->i}
    </span>
    min";
        $this->assertSame($expected, $nextDrawFormatted);
    }

    /** @test */
    public function getNextDrawFormattedForListWidget_hoursFormat(): void
    {
        $now = Carbon::now($this->lottery->timezone);
        $this->lottery->lastDateLocal = $now->format(Helpers_Time::DATETIME_FORMAT);
        $this->lottery->nextDateLocal = null;
        $this->lottery->nextDateUtc = null;
        $this->lottery->drawDates = [
            $now->addDay()->format('D H:i')
        ];
        $this->lottery->save();

        $countdown = Lotto_View::next_draw_countdown($this->lottery->to_array());
        $nextDrawFormatted = $this->widgetService->getNextDrawFormattedForListWidget($this->lottery->to_array());
        $expected = "    <span class=\"countdown-item\">
        {$countdown->h}
    </span>
    godz
    <span class=\"countdown-item\">
        {$countdown->i}
    </span>
    min";
        $this->assertSame($expected, $nextDrawFormatted);
    }

    /** @test */
    public function getLastResultsHtml_emptyLotteries(): void
    {
        $lastResultsHtml = $this->widgetService->getLastResultsHtml([], '', '', '');
        $this->assertSame('    <div class="small-widget-no-info">
        Brak ostatnich wyników.
    </div>', $lastResultsHtml);
    }

    /** @test */
    public function getLastResultsHtml_withLotteries(): void
    {
        $this->lottery->lastDateLocal = '2022-03-02 12:00:00';
        $this->lottery->lastNumbers = "3,6,11,14,66";
        $this->lottery->lastBnumbers = "21";
        $this->lottery->save();

        $lotteries = [$this->lottery->to_array()];

        $expected = <<<HTML
 <div class="small-widget-content small-widget-results-items">    <div class="small-widget-results-item">
        <div class="pull-left">
            1
                <a href="/test/powerball/">
                    Powerball
                </a>
            2
        </div>
        <div class="pull-right small-widget-results-date">
            <span class="fa fa-clock-o" aria-hidden="true"></span>
            2 marca
        </div>
        <div class="clearfix"></div>
        <div class="ticket-line">
    <div class="ticket-line-number">3</div>
    <div class="ticket-line-number">6</div>
    <div class="ticket-line-number">11</div>
    <div class="ticket-line-number">14</div>
    <div class="ticket-line-number">66</div>
    <div class="ticket-line-bnumber">21</div></div>
    </div></div>
HTML;

        $lastResultsHtml = $this->widgetService->getLastResultsHtml($lotteries, '1', '2', '/test');
        $this->assertSame($expected, $lastResultsHtml);
    }
}
