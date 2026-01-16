<?php

namespace Services;

use Exception;
use Fuel\Core\Database_Query_Builder;
use GuzzleHttp\Client;
use Helpers_Ltech;
use Helpers_Time;
use Services\Logs\FileLoggerService;
use Throwable;
use Wrappers\Db;

class LtechService
{
    /**
     * This is the biggest full cost of lottery's line per currency
     * 50 means 0 on ltech, so 52.25 in USD means that 2.25 is the biggest
     * full cost of some lottery per line, and it means this is the minimum balance
     * that should be on ltech, if it's not we do not buy tickets
     */
    private const MIN_ALLOWED_AMOUNT_PER_CURRENCY = [
        'USD' => 52.25,
        'EUR' => 52.50,
        'GBP' => 52.20,
        'PLN' => 53.45,
        'AUD' => 51.40,
        'BRL' => 54.80,
        'ZMW' => 55.00,
        'PEN' => 51.00,
        'HUF' => 300.00
    ];

    public const CACHE_KEY = 'ltech_balances';

    private Client $httpClient;
    private Helpers_Ltech $helpersLtech;
    private FileLoggerService $logger;
    private CacheService $cacheService;
    private Db $db;

    public function __construct(
        Client $httpClient,
        Helpers_Ltech $helpersLtech,
        FileLoggerService $logger,
        CacheService $cacheService,
        Db $db
    ) {
        $this->httpClient = $httpClient;
        $this->helpersLtech = $helpersLtech;
        $this->logger = $logger;
        $this->cacheService = $cacheService;
        $this->db = $db;
    }

    /**
     * Return keys as currencies and values as current balance
     * We check balance only for default Ltech Account
     */
    public function getCurrentBalances(): array
    {
        return $this->cacheService->getAndSaveCacheGlobalWithHandleException(
            self::CACHE_KEY,
            fn() => $this->fetchCurrentBalances(),
            [],
            Helpers_Time::TEN_MINUTES_IN_SECONDS
        );
    }

    private function fetchCurrentBalances(): array
    {
        try {
            /**
             * Get only default Ltech Account details
             * Sometimes whitelabel can have own LtechAccount, but we not use it here
             * We check balances only on our default Ltech account
             **/
            $ltechDetails = $this->helpersLtech->get_ltech_details();
            [
                'endpoint' => $endpoint,
                'key' => $key
            ] = $ltechDetails;

            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache'
                ],
                'auth' => [$key, null]
            ];
            $response = $this->httpClient->get($endpoint . 'account', $options);

            $isError = $response->getStatusCode() !== 200;
            if ($isError) {
                $this->logger->error(
                    'Something wrong with communication with Ltech. Response code: ' . $response->getStatusCode()
                );
                return [];
            }

            $data = json_decode($response->getBody()->getContents(), true);
            $isEmptyData = !key_exists('accounts', $data);
            if ($isEmptyData) {
                $this->logger->error(
                    'Ltech returned empty data instead of balance'
                );
                return [];
            }

            $balances = [];
            foreach ($data['accounts'] as $account) {
                $isNotTicketBalance = substr($account['name'], 0, 7) !== "Tickets";
                if ($isNotTicketBalance) {
                    continue;
                }

                $balance = round($account['balance'], 2);
                $balances[$account['currency']] = $balance;
            }

            return $balances;
        } catch (Throwable $exception) {
            $this->logger->error('Cannot get current balances from ltech. Message: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Enough balance means that on Ltech account in specific currency we have enough money
     * to buy the most expensive line in each currency.
     *
     * Sometimes ticket has many lines, so our limit will be false positive (e.g. ltech balance is 3,
     * min_allowed_amount 2, and someone tries to buy ticket with two lines for 4, we still will try to buy it, but
     * we know that there is too low balance, but when someone buys ticket for 2, balance will decrease to 1 and our
     * limit will work)
     * @return string[]
     * @throws Exception
     */
    public function getCurrenciesWithEnoughBalance(): array
    {
        $allowedCurrencies = [];
        // If this variable is empty it means there was a problem to fetch data from Ltech
        $currentBalances = $this->getCurrentBalances();
        if (empty($currentBalances)) {
            throw new Exception('Ltech - Fetching balances error');
        }

        foreach ($currentBalances as $currency => $balance) {
            /** This should not happen, but when we add any new currency to ltech, some default value should be set*/
            $minAllowedBalance = self::MIN_ALLOWED_AMOUNT_PER_CURRENCY[$currency] ?? 5.0;
            if ($balance >= $minAllowedBalance) {
                $allowedCurrencies[] = $currency;
            }
        }

        return $allowedCurrencies;
    }

    /** First group by whitelabel next by currency */
    public function getSumsOfQueuedTicketPerCurrency(): array
    {
        // wut.model = 3 - these are LCS tickets
        $rawQuery = "SELECT MIN(w.name) AS whitelabel, c.code AS currency_code, SUM(wut.cost_local) AS cost
        FROM whitelabel_user_ticket wut
        LEFT JOIN whitelabel w
        ON wut.whitelabel_id = w.id
        LEFT JOIN lottery l
        ON l.id = wut.lottery_id
        LEFT JOIN currency c
        ON c.id = l.currency_id
        WHERE wut.id IN (SELECT DISTINCT wut.id AS id
            FROM whitelabel_user_ticket_line wutl
            INNER JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            INNER JOIN lottery_provider lp ON lp.id = wut.lottery_provider_id
            INNER JOIN lottery lot ON lot.id = wut.lottery_id
            INNER JOIN currency c ON lot.currency_id = c.id
            INNER JOIN whitelabel_lottery wl ON wut.whitelabel_id = wl.whitelabel_id AND wut.lottery_id = wl.lottery_id
            LEFT JOIN lottorisq_ticket lt ON lt.whitelabel_user_ticket_slip_id = wutl.whitelabel_user_ticket_slip_id
            LEFT JOIN whitelabel w ON w.id = wut.whitelabel_id
            WHERE wutl.whitelabel_user_ticket_slip_id IS NOT NULL
                AND wut.paid = 1 
                AND wut.status = 0 
                AND wut.date_processed IS NULL
                AND provider = 1 
                AND wut.model != 3
                AND lt.id IS NULL 
                AND lot.next_date_local >= wut.draw_date
                AND wut.whitelabel_id != 20)
        GROUP BY w.name, c.code
        ORDER BY CASE whitelabel
        WHEN 'MegaJackpotPH' THEN 1
        WHEN 'MegaJackpot' THEN 2
        WHEN 'RedFoxLotto' THEN 3
        WHEN 'LottoHoy' THEN 4
        WHEN 'LottoMat' THEN 5
        WHEN 'DoubleJack.Online' THEN 6
        WHEN 'LottoPark' THEN 7
        ELSE 8 END ASC";

        /** @var Database_Query_Builder $query */
        $query = $this->db->query($rawQuery)->execute();
        return $query->as_array();
    }
}
