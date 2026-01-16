<?php

declare(strict_types=1);

namespace Feature\Classes\Models;

use Models\Lottery;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Model_Whitelabel_Transaction;
use Carbon\Carbon;
use Helpers_General;
use Fuel\Tasks\Factory\Utils\Faker;
use Repositories\CurrencyRepository;
use Repositories\LotteryRepository;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelTransactionFixture;
use Tests\Fixtures\WhitelabelUserFixture;
use Tests\Fixtures\WhitelabelUserTicketFixture;
use Test_Feature;

final class ModelWhitelabelTransactionTest extends Test_Feature
{
    private WhitelabelFixture $whitelabelFixture;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelUserTicketFixture $whitelabelUserTicketFixture;
    private WhitelabelTransactionFixture $whitelabelTransactionFixture;
    private Whitelabel $whitelabel;
    private WhitelabelUser $whitelabelUser;
    private CurrencyRepository $currencyRepository;
    private LotteryRepository $lotteryRepository;

    private ?int $languageId = null;
    private ?string $country = null;
    private string $queryFilters = '';
    private array $queryParams = [];
    private array $regularTicket;
    private array $bonusTicket;

    private array $dateRangeYesterday = ['yesterday 00:00:00', 'yesterday 23:59:59'];
    private array $dateRangeToday = ['today 00:00:00', 'today 23:59:59'];
    private array $languageAndCountryFiltersDefault = [1, 'PL'];
    private array $languageAndCountryFiltersWrongLanguageId = [2, 'PL'];
    private array $languageAndCountryFiltersWrongCountry = [1, 'FR'];

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelUserTicketFixture = $this->container->get(WhitelabelUserTicketFixture::class);
        $this->whitelabelTransactionFixture = $this->container->get(WhitelabelTransactionFixture::class);
        $this->currencyRepository = $this->container->get(CurrencyRepository::class);
        $this->lotteryRepository = $this->container->get(LotteryRepository::class);

        $currency = $this->currencyRepository->findOneByCode('EUR');

        $this->whitelabel = $this->whitelabelFixture->createOne([
            'manager_site_currency_id' => $currency->id
        ]);

        $this->whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'currency_id' => $currency->id,
                'country' => 'PL'
            ]);

        $this->faker = $this->container->get(Faker::class);
    }

    public function getSumsSalesForReportsDataProvider(): array
    {
        $this->regularTicket = json_decode(file_get_contents(__DIR__ . '/powerball-ticket.json'), true);
        $this->bonusTicket = json_decode(file_get_contents(__DIR__ . '/powerball-bonus-ticket.json'), true);

        $emptyExpected = [
            'amount_manager' => null,
            'income_manager' => null,
            'cost_manager' => null,
            'margin_manager' => '0.00',
            'payment_cost_manager' => null,
        ];

        $regularTicketsOnlyExpected = [
            'amount_manager' => '16.44',
            'income_manager' => '5.06',
            'cost_manager' => '11.36',
            'margin_manager' => '1.02',
            'payment_cost_manager' => '0.00',
        ];

        $bonusTicketsOnlyExpected = [
            'amount_manager' => null,
            'income_manager' => null,
            'cost_manager' => null,
            'margin_manager' => '0.76',
            'payment_cost_manager' => null,
        ];

        $regularAndBonusExpected = [
            'amount_manager' => '16.44',
            'income_manager' => '5.06',
            'cost_manager' => '11.36',
            'margin_manager' => '1.78',
            'payment_cost_manager' => '0.00',
        ];

        $regularAndBonusTickets = [$this->regularTicket, $this->bonusTicket, $this->regularTicket, $this->bonusTicket];
        $regularTicketsOnly = [$this->regularTicket, $this->regularTicket];
        $bonusTicketsOnly = [$this->bonusTicket, $this->bonusTicket];

        return [
            'no tickets' => [[], $emptyExpected, $this->dateRangeToday, $this->languageAndCountryFiltersDefault],
            'tickets out of date range' => [$regularAndBonusTickets, $emptyExpected, $this->dateRangeYesterday, $this->languageAndCountryFiltersDefault],
            'tickets out of filters range 1' => [$regularAndBonusTickets, $emptyExpected, $this->dateRangeToday, $this->languageAndCountryFiltersWrongLanguageId],
            'tickets out of filters range 2' => [$regularAndBonusTickets, $emptyExpected, $this->dateRangeToday, $this->languageAndCountryFiltersWrongCountry],
            'regular tickets only' => [$regularTicketsOnly, $regularTicketsOnlyExpected, $this->dateRangeToday, $this->languageAndCountryFiltersDefault],
            'bonus tickets only' => [$bonusTicketsOnly, $bonusTicketsOnlyExpected, $this->dateRangeToday, $this->languageAndCountryFiltersDefault],
            'regular and bonus tickets' => [$regularAndBonusTickets, $regularAndBonusExpected, $this->dateRangeToday, $this->languageAndCountryFiltersDefault],
        ];
    }

    public function getSumsSalesForAdminReportsDataProvider(): array
    {
        $this->regularTicket = json_decode(file_get_contents(__DIR__ . '/powerball-ticket.json'), true);
        $this->bonusTicket = json_decode(file_get_contents(__DIR__ . '/powerball-bonus-ticket.json'), true);

        $emptyExpected = [
            'amount_manager' => '0.00',
            'income_manager' => '0.00',
            'cost_manager' => '0.00',
            'margin_manager' => '0.00',
            'payment_cost_manager' => '0.00',
            'amount_usd' => '0.00',
            'income_usd' => '0.00',
            'cost_usd' => '0.00',
            'margin_usd' => '0.00',
            'payment_cost_usd' => '0.00',
        ];

        $regularTicketsOnlyExpected = [
            'amount_manager' => '16.44',
            'income_manager' => '5.06',
            'cost_manager' => '11.36',
            'margin_manager' => '1.02',
            'payment_cost_manager' => '0.00',
            'amount_usd' => '19.50',
            'income_usd' => '6.00',
            'cost_usd' => '13.50',
            'margin_usd' => '1.20',
            'payment_cost_usd' => '0.00',
        ];

        $bonusTicketsOnlyExpected = [
            'amount_manager' => '0.00',
            'income_manager' => '0.00',
            'cost_manager' => '0.00',
            'margin_manager' => '0.76',
            'payment_cost_manager' => '0.00',
            'amount_usd' => '0.00',
            'income_usd' => '0.00',
            'cost_usd' => '0.00',
            'margin_usd' => '0.00',
            'payment_cost_usd' => '0.00',
        ];

        $regularAndBonusExpected = [
            'amount_manager' => '16.44',
            'income_manager' => '5.06',
            'cost_manager' => '11.36',
            'margin_manager' => '1.78',
            'payment_cost_manager' => '0.00',
            'amount_usd' => '19.50',
            'income_usd' => '6.00',
            'cost_usd' => '13.50',
            'margin_usd' => '1.20',
            'payment_cost_usd' => '0.00',
        ];

        $regularAndBonusTickets = [$this->regularTicket, $this->bonusTicket, $this->regularTicket, $this->bonusTicket];
        $regularTicketsOnly = [$this->regularTicket, $this->regularTicket];
        $bonusTicketsOnly = [$this->bonusTicket, $this->bonusTicket];

        return [
            'no tickets' => [[], $emptyExpected, $this->dateRangeToday, $this->languageAndCountryFiltersDefault],
            'tickets out of date range' => [$regularAndBonusTickets, $emptyExpected, $this->dateRangeYesterday, $this->languageAndCountryFiltersDefault],
            'tickets out of filters range 1' => [$regularAndBonusTickets, $emptyExpected, $this->dateRangeToday, $this->languageAndCountryFiltersWrongLanguageId],
            'tickets out of filters range 2' => [$regularAndBonusTickets, $emptyExpected, $this->dateRangeToday, $this->languageAndCountryFiltersWrongCountry],
            'regular tickets only' => [$regularTicketsOnly, $regularTicketsOnlyExpected, $this->dateRangeToday, $this->languageAndCountryFiltersDefault],
            'bonus tickets only' => [$bonusTicketsOnly, $bonusTicketsOnlyExpected, $this->dateRangeToday, $this->languageAndCountryFiltersDefault],
            'regular and bonus tickets' => [$regularAndBonusTickets, $regularAndBonusExpected, $this->dateRangeToday, $this->languageAndCountryFiltersDefault],
        ];
    }

    /**
     * @test
     * @dataProvider getSumsSalesForReportsDataProvider
     */
    public function getSumsSalesForReports(
        array $ticketsToAdd,
        array $expected,
        array $dateRange,
        array $languageAndCountryFilters,
    ): void {
        foreach ($ticketsToAdd as $ticket) {
            $lottery = $this->lotteryRepository->findOneById($ticket['lottery_id']);
            $this->prepareUserTicketFixture($lottery, $ticket['bonus'])->createOne($ticket['attributes']);
        }

        $this->languageId = $languageAndCountryFilters[0];
        $this->country = $languageAndCountryFilters[1];

        $this->addDatesRangeFilter(...$dateRange);
        $this->addLanguageAndCountryQueryFilters();

        $saleAmountsResult = Model_Whitelabel_Transaction::get_sums_sales_for_reports(
            $this->queryFilters,
            $this->queryParams,
            $this->whitelabel->id
        );

        $actual = current($saleAmountsResult);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider getSumsSalesForAdminReportsDataProvider
     */
    public function getSumsSalesForAdminReports(
        array $ticketsToAdd,
        array $expected,
        array $dateRange,
        array $languageAndCountryFilters
    ): void {
        foreach ($ticketsToAdd as $ticket) {
            $lottery = $this->lotteryRepository->findOneById($ticket['lottery_id']);
            $this->prepareUserTicketFixture($lottery, $ticket['bonus'])->createOne($ticket['attributes']);
        }

        $this->languageId = $languageAndCountryFilters[0];
        $this->country = $languageAndCountryFilters[1];

        $this->addDatesRangeFilter(...$dateRange);
        $this->addLanguageAndCountryQueryFilters();

        $saleAmountsResult = Model_Whitelabel_Transaction::get_sums_sales_for_admin_reports(
            $this->queryFilters,
            $this->queryParams,
            $this->whitelabel->type,
            $this->whitelabel->id
        );

        $actual = current($saleAmountsResult);

        $this->assertEquals($expected, $actual);
    }

    private function prepareUserTicketFixture(Lottery $lottery, bool $bonus = false): WhitelabelUserTicketFixture
    {
        $this->whitelabelUserTicketFixture->reset();

        $ticketFixture = $this->whitelabelUserTicketFixture
            ->withWhitelabel($this->whitelabel)
            ->withUser($this->whitelabelUser)
            ->withCurrency($this->whitelabelUser->currency)
            ->withLottery($lottery)
            ->withDateTimeNow();

        if (!$bonus) {
            $whitelabelTransaction = $this->whitelabelTransactionFixture
                ->withWhitelabel($this->whitelabel)
                ->withUser($this->whitelabelUser)
                ->withCurrency($this->whitelabelUser->currency)
                ->createOne([
                    'type' => Helpers_General::TYPE_TRANSACTION_PURCHASE,
                    'status' => Helpers_General::STATUS_TRANSACTION_APPROVED
                ]);

            $ticketFixture->withTransaction($whitelabelTransaction);
        }

        return $ticketFixture;
    }

    private function addLanguageAndCountryQueryFilters(): void
    {
        $filterAddPrepare = [];

        if ($this->languageId !== null) {
            $filterAddPrepare[] = ' AND whitelabel_user.language_id = :language ';
            $this->queryParams[] = [':language', $this->languageId];
        }

        if ($this->country !== null) {
            $filterAddPrepare[] = ' AND whitelabel_user.country = :country ';
            $this->queryParams[] = [':country', $this->country];
        }

        $this->queryFilters .= implode('', $filterAddPrepare);
    }

    public function addDatesRangeFilter(string $dateStartString, string $dateEndString, string $columnName = 'date'): void
    {
        $dateStart = Carbon::parse($dateStartString)->format('Y-m-d H:i:s');
        $dateEnd = Carbon::parse($dateEndString)->format('Y-m-d H:i:s');

        $filterDates[] = " AND " . $columnName . " >= :date_start ";
        $this->queryParams[] = [":date_start", $dateStart];

        $filterDates[] = " AND " . $columnName . " <= :date_end ";
        $this->queryParams[] = [":date_end", $dateEnd];

        $this->queryFilters .= implode("", $filterDates);
    }
}
