<?php

namespace Services\LotteryProvider;

use Carbon\Carbon;
use Container;
use Exception;
use Fuel\Core\Config;
use Helpers_Multidraw;
use Helpers_Time;
use Model_Lottery;
use Model_Lottorisq_Ticket;
use Model_Whitelabel_User_Ticket;
use Model_Whitelabel_User_Ticket_Slip;
use Repositories\LottorisqLogRepository;
use Repositories\WhitelabelLotteryProviderApiRepository;
use Services\Logs\FileLoggerService;
use Throwable;

class TheLotterScanTicketService
{
    private LottorisqLogRepository $lottorisqLogRepository;
    private FileLoggerService $logger;

    const SUCCESS_RESULT_CODE = '1';
    const SUCCESS_RESULT_DESCRIPTION = 'Confirmed';

    public function __construct()
    {
        $this->lottorisqLogRepository = Container::get(LottorisqLogRepository::class);
        $this->logger = Container::get(FileLoggerService::class);
    }

    /** @throws Exception */
    public function confirmScan(array $responseData): void
    {
        if (empty($responseData)) {
            throw new Exception('[TheLotter Scan confirm] Empty response data.');
        }

        if (empty($responseData['customer_tag'])) {
            throw new Exception('[TheLotter Scan confirm] Empty customer tag.');
        }

        if (empty($responseData['request_id'])) {
            throw new Exception('[TheLotter Scan confirm] Empty request id.');
        }

        if ($responseData['is_ticket_scanned'] === false || empty($responseData['scanned_url'])) {
            return; // we're skipping it because there's no scan available yet
        }

        if ($responseData['result_description'] !== self::SUCCESS_RESULT_DESCRIPTION || $responseData['result_code'] !== self::SUCCESS_RESULT_CODE) {
            throw new Exception('[TheLotter Scan confirm] Bad response status. ResponseData: ' . json_encode($responseData));
        }

        $slip = null;
        $slipId = (int)explode('|', $responseData['customer_tag'])[0];
        $apiSecretFromResponse = explode('|', $responseData['customer_tag'])[1];

        if (empty($slipId) || empty($apiSecretFromResponse)) {
            throw new Exception('[TheLotter Scan confirm] Bad customer tag format. ResponseData: ' . json_encode($responseData));
        }

        try {
            Config::load('lottorisq', true);

            $slip = Model_Whitelabel_User_Ticket_Slip::find_by_pk($slipId);

            if ($slip === null) {
                throw new Exception("[TheLotter Scan Confirm] Couldn't find slip with id: $slipId");
            }

            $ticket = Model_Whitelabel_User_Ticket::find_by_pk($slip->whitelabel_user_ticket_id);

            try {
                /** @var WhitelabelLotteryProviderApiRepository $whitelabelLotteryProviderApiRepository */
                $whitelabelLotteryProviderApiRepository = Container::get(WhitelabelLotteryProviderApiRepository::class);
                $theLotterApiDetails = $whitelabelLotteryProviderApiRepository->getApiDetailsByWhitelabelId($ticket['whitelabel_id']);
            } catch (Throwable $exception) {
                $this->logger->error(
                    "TheLotter API data retrieval failed. WhitelabelId: {$ticket['whitelabel_id']}, Error message: {$exception->getMessage()}"
                );
                return;
            }

            // api validation
            $secretHash = md5($theLotterApiDetails['api_secret'] . $ticket['whitelabel_id']);
            if ($responseData['customer_tag'] != $slip->id . '|' . $secretHash) {
                throw new Exception('[TheLotter Scan confirm] Incorrect API secret key.');
            }

            // @phpstan-ignore-next-line
            $lottorisq = Model_Lottorisq_Ticket::find_by_lottorisqid($responseData['request_id']);
            if ($lottorisq === null || count($lottorisq) == 0) {
                throw new Exception('[TheLotter Scan Confirm] Can\'t find Lottorisq Ticket entry.');
            }

            $lottorisq = $lottorisq[0];
            $lottorisq->set([
                'confirm_data' => serialize($responseData)
            ]);
            $lottorisq->save();

            $lottery = Model_Lottery::find_by_pk($ticket->lottery_id);
            $drawDate = Carbon::parse($ticket->draw_date, $lottery->timezone);
            $drawDateFormatted = $drawDate->format(Helpers_Time::DATETIME_FORMAT);

            // Add ticket scan if exist
            if (!empty($responseData['is_ticket_scanned'])) {
                $slip_update = $slip;
                $slip_update->set([
                    'ticket_scan_url' => $responseData['scanned_url']
                ]);
                $slip_update->save();
            }

            // Multidraw check
            if (!empty($ticket->multi_draw_id)) {
                $multidrawHelper = new Helpers_Multidraw([]);
                $multidrawHelper->check_multidraw_ltech_confirmation(
                    $ticket->multi_draw_id,
                    $drawDateFormatted
                );
            }

            $this->lottorisqLogRepository->addSuccessLog(
                $ticket->whitelabel_id,
                $ticket->id,
                $slip->id,
                'Received confirmation of ticket from Lottorisq.',
                $responseData,
                $slip->whitelabel_ltech_id
            );
        } catch (Exception $e) {
            http_response_code(400);
            $err_slip = null;
            $err_ticket = null;
            $err_whitelabel = null;
            if ($slip !== null) {
                $err_slip = $slip->id;
                $ticket = Model_Whitelabel_User_Ticket::find_by_pk($slip->whitelabel_user_ticket_id);
                $err_ticket = $ticket->id;
                $err_whitelabel = $ticket->whitelabel_id;
            }
            $this->lottorisqLogRepository->addErrorLog(
                $err_whitelabel,
                $err_ticket,
                $err_slip,
                $e->getMessage(),
                null,
                $slip->whitelabel_ltech_id ?? null
            );
        }
    }
}
