<?php

namespace Services\LotteryProvider;

use Carbon\Carbon;
use Container;
use Email\Email;
use Exception;
use Fuel\Core\Config;
use Fuel\Core\DB;
use Fuel\Core\Package;
use Lotto_Helper;
use Model_Lottery;
use Model_Lottery_Provider;
use Model_Lottorisq_Ticket;
use Model_Setting;
use Model_Whitelabel;
use Model_Whitelabel_User_Ticket;
use Model_Whitelabel_User_Ticket_Line;
use Model_Whitelabel_User_Ticket_Slip;
use Repositories\LottorisqLogRepository;
use Repositories\WhitelabelLotteryProviderApiRepository;
use Services\Logs\FileLoggerService;
use Services\LotteryProvider\Api\TheLotterApiClient;
use Throwable;

class TheLotterPurchaseTicketService
{
    private TheLotterApiClient $theLotterApiClient;
    private FileLoggerService $logger;
    private WhitelabelLotteryProviderApiRepository $whitelabelLotteryProviderApiRepository;

    private string $processLottorisqSlipStatus = 'OK';

    private const MAX_TICKETS_PER_TASK = 300;

    public function __construct()
    {
        $this->theLotterApiClient = new TheLotterApiClient();
        $this->logger = Container::get(FileLoggerService::class);
        $this->whitelabelLotteryProviderApiRepository = Container::get(WhitelabelLotteryProviderApiRepository::class);
    }

    private function loadConfigByFileName(string $fileName): void
    {
        Config::load($fileName, true);
    }

    private function sendSlipPurchaseFailedEmail(string $errorMessage): void
    {
        Package::load('email');

        $this->loadConfigByFileName('lotteries');
        $recipients = Config::get('lotteries.emergency_emails');
        $email = Email::forge();
        $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
        $email->to($recipients);
        $title = 'Lotto Emergency: Lottorisq Slip Failed';
        $email->subject($title);

        $body_text = 'Unknown error! More info [task.php/purchasetickets]: ' .
            $errorMessage;
        $email->body($body_text);

        try {
            $email->send();
        } catch (Exception $e) {
            $errorMessage = 'There is a problem with delivering the mail. ' .
                'Description of error: ' . $e->getMessage();
            $this->logger->error($errorMessage);
        }
    }

    /**
     * This method return query to get SLIPS
     * In first order we get slips for megajackpot.ph, megajackpot, redfox, lottohoy, lottomat, doublejack.online, lottopark
     * Next we order slips by the nearest draw date
     * We limit to get max 500 slips per task (task is run every minute)
     * wut.model = 3 means LCS tickets and we don't want to process it here
     */
    private function getAllSlipsQuery(): string
    {
        return <<<'SQL'
            SELECT 
                DISTINCT wutl.whitelabel_user_ticket_slip_id,
                wut.*,
                lot.next_date_utc,
                lot.timezone as lottery_timezone,
                lp.timezone as provider_timezone,
                lp.offset as provider_offset,
                lp.closing_times as provider_closing_times,
                lp.closing_time as provider_closing_time
            FROM whitelabel_user_ticket_line wutl
            INNER JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            INNER JOIN lottery_provider lp ON lp.id = wut.lottery_provider_id
            INNER JOIN lottery lot ON lot.id = wut.lottery_id
            INNER JOIN currency c ON lot.currency_id = c.id
            INNER JOIN whitelabel_lottery wl ON wut.whitelabel_id = wl.whitelabel_id AND wut.lottery_id = wl.lottery_id
            LEFT JOIN lottorisq_ticket lt ON lt.whitelabel_user_ticket_slip_id = wutl.whitelabel_user_ticket_slip_id
            WHERE wutl.whitelabel_user_ticket_slip_id IS NOT NULL
                AND wut.paid = 1
                AND wut.status = 0
                AND wut.date_processed IS NULL
                AND provider = 1
                AND wut.model != 3
                AND lt.id IS NULL
                AND lot.next_date_local >= wut.draw_date
                AND lot.slug IN :supportedLotterySlugs
                AND wut.whitelabel_id IN :supportedWhitelabelIds
            ORDER BY CASE wut.whitelabel_id
                WHEN 40 THEN 1
                WHEN 35 THEN 2
                WHEN 8 THEN 3
                WHEN 3 THEN 4
                WHEN 2 THEN 5
                WHEN 26 THEN 6
                WHEN 1 THEN 7
                ELSE 8
            END ASC,
            lot.next_date_utc
            LIMIT :limit
        SQL;
    }

    private function prepareAdditionalNumbers(string $bNumbers, ?int $reintegroNumber): array
    {
        $additionalNumbers = [];
        if (!empty($bNumbers)) {
            $additionalNumbers = array_map('intval', explode(',', $bNumbers));
        } else if(!is_null($reintegroNumber)) {
            $additionalNumbers = [$reintegroNumber];
        }

        return $additionalNumbers;
    }

    private function processLottorisqSlip(
        array $lottery,
        object $whitelabel,
        object $ticket,
        object $slip,
        array $slipLines,
    ): void
    {
        try {
            $theLotterApiDetails = $this->whitelabelLotteryProviderApiRepository->getApiDetailsByWhitelabelId($whitelabel['id']);
        } catch (Throwable $exception) {
            $this->logger->error(
                "TheLotter API data retrieval failed. WhitelabelId: {$whitelabel['id']}, Error message: {$exception->getMessage()}"
            );
            return;
        }

        // abort the task and do not purchase tickets if the given whitelabel does not have API data or if the API is disabled
        if (empty($theLotterApiDetails) || $theLotterApiDetails['is_enabled'] !== '1') {
            return;
        }

        // spanish lotteries require the generation of an additional Reintegro number
        $reintegroNumber = null;
        if (in_array($lottery['slug'], ['el-gordo-primitiva', 'la-primitiva', 'bonoloto'])) {
            if ($slip->additional_data) {
                $additionalData = unserialize($slip->additional_data);
                if (
                    isset($additionalData['refund']) &&
                    is_numeric($additionalData['refund'])
                ) {
                    $reintegroNumber = $additionalData['refund'];
                } else {
                    $reintegroNumber = Lotto_Helper::get_random_number();
                    $slip->additional_data = serialize(['refund' => $reintegroNumber]);
                    $slip->save();
                }
            }
        }

        // we clear the reintegro number for the Bonoloto lottery because their API does not yet support this number
        if ($lottery['slug'] == 'bonoloto') {
            $reintegroNumber = null;
        }

        $ticketLines = [];
        foreach ($slipLines as $line) {
            $additionalNumbers = $this->prepareAdditionalNumbers($line->bnumbers, $reintegroNumber);
            $ticketLines[] = [
                'regular_numbers' => array_map('intval', explode(',', $line->numbers)),
                'additional_numbers' => $additionalNumbers,
            ];
        }

        $secretHash = md5($theLotterApiDetails['api_secret'] . $whitelabel['id']);
        $requestData = [
            'content-type' => 'application/json',
            'customer_id' => $theLotterApiDetails['api_key'],
            'customer_secret' => $theLotterApiDetails['api_secret'],
            'customer_tag' => $slip->id . '|' . $secretHash,
            'lottery_id' => TheLotterLotteryMap::getLotteryIdBySlug($lottery['slug']),
            'draw_local_date' => Carbon::parse($ticket['draw_date'])->format('Y-m-d'),
            'is_multiply' => false, // If the lottery supports multiply prizes, you can pass true if you wish to purchase a ticket with multiply prizes.
            'form_type' => 1, // 1 -> regular, 2 -> systematic
            'regular_block_count' => count($slipLines), // how many lines to purchase, mandatory if form_type is regular (1)
            //'systematic_form_type' => 9, // If Form Type is “Systematic”, then parameter is Mandatory. It means what systematic form to purchase.
            'ticket_lines' => $ticketLines,
            'sub_site_id' => 1, // The sub-site in which the ticket will be purchased. Sub-site 1 contains all the supported lotteries
            'scanned_ticket_notifictaion_url' => $theLotterApiDetails['scan_confirm_url'],
        ];
        $response = $this->theLotterApiClient->purchaseTicket($requestData);

        if ($response['request_status'] === 'OK') {
            if (empty($response['date_created_utc'])) {
                $this->logger->error(
                    "TheLotter API - cannot purchase tickets, API refusal. WhitelabelId: {$ticket['whitelabel_id']}, TicketToken: {$ticket['token']}, LotteryId: {$ticket['lottery_id']}"
                );
                $this->processLottorisqSlipStatus = 'NOK';
                return;
            }
            $risqTicket = Model_Lottorisq_Ticket::forge();
            $risqTicket->set([
                'whitelabel_user_ticket_slip_id' => $slip->id,
                'lottorisqid' => $response['request_id']
            ]);
            $risqTicket->save();
            /** @var LottorisqLogRepository $lottorisqLogRepository */
            $lottorisqLogRepository = Container::get(LottorisqLogRepository::class);
            $lottorisqLogRepository->addSuccessLog(
                $whitelabel['id'],
                $ticket->id,
                $slip->id,
                'Ticket (slip) created at Lottorisq system with id: ' . $response['request_id'] . '.',
                [$response, $requestData],
                $theLotterApiDetails['id']
            );
        } else if($response['request_status'] === 'NOK') {
            if ($response['result_code'] == '1001') { // blocked access to the API, likely due to insufficient balance in the lotter account
                $this->processLottorisqSlipStatus = 'NOK';
                return;
            }

            $this->logger->error(
                "[TheLotter] The ticket was not purchased. WhitelabelId: {$whitelabel['id']}, TicketToken: {$ticket['token']}, API response: " . json_encode($response)
            );
            $this->processLottorisqSlipStatus = 'NOK';
        }
    }

    public function purchaseTickets(): void
    {
        $getAllSlipsQuery = $this->getAllSlipsQuery();
        try {
            $supportedWhitelabelIds = $this->whitelabelLotteryProviderApiRepository->getWhitelabelIdsWithApiEnabled();
        } catch (Throwable $exception) {
            $this->logger->error(
                "TheLotter API - Unable to Retrieve whitelabelIds. Error message: {$exception->getMessage()}"
            );
            return;
        }

        try {
            // check if there's any lottorisq slip failed and retry if there's still time to do that - if not, inform us about
            // it will catch any connection errors too - if the slip is already created within lottorisq system it will not create another one
            // wut.model=3 => this is none. I do not know if we will have multiple providers, we had invalap and lottorisq before
            // now we have only lottorisq, so I added model None (3) to lottorisq
            $limit = self::MAX_TICKETS_PER_TASK;
            $supportedLotterySlugs = TheLotterLotteryMap::getAllLotterySlugsByTheLotterProvider();
            $tickets = DB::query($getAllSlipsQuery)
                ->bind('supportedLotterySlugs', $supportedLotterySlugs)
                ->bind('supportedWhitelabelIds', $supportedWhitelabelIds)
                ->bind('limit', $limit)
                ->execute();

            $isAnyTicketToProcess = $tickets !== null && count($tickets) > 0;
            if ($isAnyTicketToProcess) {
                foreach ($tickets as $ticket) { // ticket means lottorisq slip here
                    $closing_time_date = Lotto_Helper::calculate_closing_time($ticket['lottery_id'], $ticket['lottery_timezone'], Model_Lottery_Provider::forge([
                        'timezone' => $ticket['provider_timezone'],
                        'offset' => $ticket['provider_offset'],
                        'closing_times' => $ticket['provider_closing_times'],
                        'closing_time' => $ticket['provider_closing_time'],
                    ]), $ticket['draw_date']);

                    $now = Carbon::now();
                    $now->addMinutes(10);

                    $whitelabel = Model_Whitelabel::find_by_pk($ticket['whitelabel_id']);
                    $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
                    // in this case we only need id
                    $lottery = $lotteries['__by_id'][$ticket['lottery_id']];

                    $model_ticket = Model_Whitelabel_User_Ticket::find_by_pk($ticket['id']);
                    $slip = Model_Whitelabel_User_Ticket_Slip::find_by_pk($ticket['whitelabel_user_ticket_slip_id']);

                    // do not purchase tickets when you there are 10 minutes left to l-tech closing time and beyond
                    if ($now < $closing_time_date) {
                        $slip_lines = Model_Whitelabel_User_Ticket_Line::find([
                            'where' => [
                                "whitelabel_user_ticket_slip_id" => $ticket['whitelabel_user_ticket_slip_id']
                            ],
                            'order_by' => ['id' => 'asc']
                        ]);

                        try {
                            DB::start_transaction();

                            $this->processLottorisqSlip(
                                $lottery,
                                $whitelabel,
                                $model_ticket,
                                $slip,
                                $slip_lines,
                            );

                            // we're breaking the iteration; we don't want to spam API requests to lotter
                            // NOK -> API response with a failure code
                            if ($this->processLottorisqSlipStatus === 'NOK') {
                                break;
                            }

                            DB::commit_transaction();
                        } catch (\Throwable $e) {
                            DB::rollback_transaction();

                            $lottorisqLogRepository = Container::get(LottorisqLogRepository::class);
                            $lottorisqLogRepository->addErrorLog(
                                $whitelabel['id'],
                                $model_ticket->id,
                                $slip->id,
                                $e->getMessage(),
                                null,
                                $slip->whitelabel_ltech_id
                            );
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $this->sendSlipPurchaseFailedEmail($e->getMessage());
        }
    }
}
