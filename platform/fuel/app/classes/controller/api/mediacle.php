<?php

use Carbon\Carbon;
use Fuel\Core\CacheNotFoundException;
use Fuel\Core\Input;
use Fuel\Core\Request;
use Fuel\Core\Response;
use Helpers\SanitizerHelper;
use Modules\Mediacle\IsPluginEnabledSpecification;
use Modules\Mediacle\MediaclePlugin;
use Modules\Mediacle\Models\MediacleSalesData;
use Modules\Mediacle\Models\PlayerDataWhitelabelUserModelAdapter;
use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\SlotTransactionRepository;
use Services\Api\Controller;
use Services\Api\Reply;
use Services\CacheService;
use Services\Logs\FileLoggerService;
use Services\Shared\System;

class Controller_Api_Mediacle extends Controller
{
    private WhitelabelUserRepository $whitelabelUserRepository;
    private TransactionRepository $transactionRepository;
    private IsPluginEnabledSpecification $isPluginEnabled;
    private System $system;
    private FileLoggerService $fileLoggerService;
    private SlotTransactionRepository $slotTransactionRepository;
    private CacheService $cacheService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->transactionRepository = Container::get(TransactionRepository::class);
        $this->isPluginEnabled = Container::get(IsPluginEnabledSpecification::class);
        $this->system = Container::get(System::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->fileLoggerService->setSource('api');
        $this->slotTransactionRepository = Container::get(SlotTransactionRepository::class);
        $this->cacheService = Container::get(CacheService::class);
    }

    /**
     * @OA\Get(
     *     path="/mediacle/registrations",
     *     tags={"Registrations"},
     *     @OA\Parameter(
     *          name="date",
     *          in="query",
     *          description="Date in YYYY-mm-dd format",
     *          example="2020-01-01"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Transactions list"
     *     )
     * )
     *
     * This endpoint is shared for casino/lotteries
     */
    public function get_registrations(): Response
    {
        $this->verifyPluginIsEnabled();

        $results = $this->whitelabelUserRepository->findPlayerRegistrationsByDate(
            $this->getWhitelabelId(),
            $this->extractDate()
        );

        return $this->returnResponse(
            array_map(
                fn (PlayerDataWhitelabelUserModelAdapter $playerData) => [
                    'player_id' => $playerData->getPlayerId(),
                    'email' => $playerData->getEmail(),
                    'phone_number' => $playerData->getPhoneNumber() ?: 'N\A',
                    'company' => $playerData->getCompany() ?: 'N\A',
                    'tracking_id' => $playerData->getTrackingId() ?: 'N\A',
                    'btag' => $playerData->getBtag() ?: 'N\A',
                    'brand' => $playerData->getBrand(),
                    'country_code' => $playerData->getCountryCode() ?: 'N\A',
                    'accounting_opening_date' => $playerData->getAccountOpeningDate(),
                    'promocode' => $playerData->getPromoCode() ?: 'N\A',
                    'timestamp' => $playerData->getTimeStamp(),
                ],
                $results
            )
        );
    }

    public function getCasinoSales(): Response
    {
        $cacheKey = $this->extractDate()->format('Y_m_d') . '_mediacle_casino_sales';
        try {
            $transactions = $this->cacheService->getCacheForWhitelabelByDomain($cacheKey);
            return $this->returnResponse($transactions);
        } catch (CacheNotFoundException $e) {
            $deposits = $this->transactionRepository->findCasinoDepositsDataByDate(
                $this->getWhitelabelId(),
                $this->extractDate()
            );

            $transactions = $this->slotTransactionRepository->getUserSummaryForMediacle(
                $this->getWhitelabelId(),
                $this->extractDate()
            );

            $totalTransactions = array_merge($deposits, $transactions);
            $transactions = array_map(fn (MediacleSalesData $salesData) => [
                'player_id' => $salesData->getPlayerId(),
                'brand' => $salesData->getBrand(),
                'transaction_date' => $salesData->getTransactionDate(),
                'deposits' => $salesData->getDeposits(),
                'bets' => $salesData->getBets(),
                'costs' => $salesData->getCosts(),
                'payment_costs' => $salesData->getPaymentCosts(),
                'royalties' => $salesData->getRoyalties(),
                'wins' => $salesData->getWins(),
                'ggr' => $salesData->getGgr(),
                'casino_bonus_balance' => $salesData->getCasinoBonusBalance(),
                'chargebacks' => $salesData->getChargeBacks(),
                'released_bonuses' => $salesData->getReleasedBonuses(),
                'revenue' => $salesData->getRevenues(),
                'currency_rate_to_gbp' => $salesData->getCurrencyRateToGbp() ?: 'N\A',
                'tracking_id' => $salesData->getTrackingId() ?: 'N\A',
                'btag' => $salesData->getBtag() ?: 'N\A',
                'first_deposit_date' => $salesData->getFirstDepositDate() ?: 'N\A',
                'promocode' => $salesData->getPromoCode() ?: 'N\A',
                'timestamp' => $salesData->getTimeStamp(),
            ], $totalTransactions);

            $this->cacheService->setCacheForWhitelabelByDomain($cacheKey, $transactions, Helpers_Time::DAY_IN_SECONDS);
            return $this->returnResponse($transactions);
        } catch (Throwable $e) {
            $this->fileLoggerService->error('Cannot send casino sales for mediacle:' . $e->getMessage());
            return $this->returnResponse(["Unable to fetch data."], Reply::SERVICE_UNAVAILABLE);
        }
    }

    /**
     * We don't use cache here because mediacle wants to have this stats realtime.
     * They call this endpoint once per 5min
     */
    public function getLotterySales(): Response
    {
        try {
            $results = $this->transactionRepository->findSalesDataByDate(
                $this->getWhitelabelId(),
                $this->extractDate()
            );

            $transactions =
                array_map(fn (MediacleSalesData $salesData) => [
                    'player_id' => $salesData->getPlayerId(),
                    'brand' => $salesData->getBrand(),
                    'transaction_date' => $salesData->getTransactionDate(),
                    'deposits' => $salesData->getDeposits(),
                    'bets' => $salesData->getBets(),
                    'costs' => $salesData->getCosts(),
                    'payment_costs' => $salesData->getPaymentCosts(),
                    'royalties' => $salesData->getRoyalties(),
                    'wins' => $salesData->getWins(),
                    'chargebacks' => $salesData->getChargeBacks(),
                    'released_bonuses' => $salesData->getReleasedBonuses(),
                    'revenue' => $salesData->getRevenues(),
                    'currency_rate_to_gbp' => $salesData->getCurrencyRateToGbp() ?: 'N\A',
                    'tracking_id' => $salesData->getTrackingId() ?: 'N\A',
                    'btag' => $salesData->getBtag() ?: 'N\A',
                    'first_deposit_date' => $salesData->getFirstDepositDate() ?: 'N\A',
                    'promocode' => $salesData->getPromoCode() ?: 'N\A',
                    'timestamp' => $salesData->getTimeStamp(),
                ], $results);

            return $this->returnResponse($transactions);
        } catch (Throwable $e) {
            $this->fileLoggerService->error($e->getMessage() . ' ' . $e->getTraceAsString());
            return $this->returnResponse(["Unable to fetch data."], Reply::SERVICE_UNAVAILABLE);
        }
    }

    /**
     * @OA\Get(
     *     path="/mediacle/sales",
     *     tags={"Sales"},
     *     @OA\Parameter(
     *          name="date",
     *          in="query",
     *          description="Date in YYYY-mm-dd format",
     *          example="2020-01-01"
     *     ),
     *     @OA\Parameter(
     *          name="is_casino",
     *          in="query",
     *          description="Defines if is casino or lottery transaction. Default false (lottery)",
     *          example="bool value = true or false"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Sales, transactions list"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Error when trying to get current day. Report has not been prepared yet."
     *     )
     * )
     */
    public function get_sales(): Response
    {
        $this->verifyPluginIsEnabled();

        $isCasino = Input::get('is_casino', false) == 'true';
        if ($isCasino) {
            /** @var bool $isNotPreparedReport - avoid saving not ready data to cache */
            $isNotPreparedReport = $this->extractDate()->isCurrentDay();
            if ($isNotPreparedReport) {
                return $this->returnResponse(['Report has not been prepared yet.'], Reply::NOT_FOUND);
            }

            return $this->getCasinoSales();
        }

        return $this->getLotterySales();
    }

    private function getWhitelabelId(): int
    {
        return $this->whitelabel->id;
    }

    private function extractDate(): Carbon
    {
        $date = SanitizerHelper::sanitizeString(Input::get('date') ?? '');
        $date = empty($date) ? Carbon::today() : new Carbon($date);
        return $date;
    }

    private function verifyPluginIsEnabled(): void
    {
        if (!$this->isPluginEnabled->isSatisfiedBy($this->getWhitelabelId(), MediaclePlugin::NAME)) {
            throw new RuntimeException('No mediacle plugin found for this whitelabel');
        }
    }
}
