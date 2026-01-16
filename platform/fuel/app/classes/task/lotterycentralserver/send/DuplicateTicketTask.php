<?php

namespace Task\LotteryCentralServer\Send;

use Fuel\Core\Database_Query_Builder;
use Fuel\Core\DB;
use Models\WhitelabelUserTicket;
use Task_Task;

/**
 * This task handles a case where ticket has been purchased in LCS,
 * but WL does not know about it and retries ticket purchase (in many cases it blocks the synchronization of lottery tickets).
 */
final class DuplicateTicketTask extends Task_Task
{
    protected $in_transaction = true; // It is important for this task to be executed within a single transaction to ensure all fixing steps are applied to database
    private array $lcsResponse;
    private array $lcsRequestPayload;
    public const ALREADY_USED_TOKENS_ERROR_CODE = 409; // Error code used to handle idempotency (sending the same ticket to LCS more than once)

    public function __construct(array $lcsResponse, array $lcsRequestPayload)
    {
        parent::__construct();
        $this->lcsResponse = $lcsResponse;
        $this->lcsRequestPayload = $lcsRequestPayload;
    }

    /**
     * Do not catch any exceptions here. The Task_Task abstract class handles any errors and transaction commit/rollback.
     */
    public function run(): void
    {
        $this->handleAlreadyPurchasedTicket();
    }

    /**
     * Whitelotto purchase flow:
     * 1. Send 10 tickets to LCS
     * 2. 2 of the tickets are already purchased in LCS (duplicate tokens)
     * 3. LCS does NOTHING else than throwing error with already purchased tickets (it does NOT buy any other tickets)
     *
     * LCS flow:
     * 1. Receive 10 tickets from WL
     * 2. Validate that there are no tickets already purchased
     * 3. Finds 2 purchased tickets, returns 2 ticket details token => uuid and response (does NOT buy or do anything else)
     *
     * What whitelotto needs to do:
     * 1. Update ticket is_synchronized = 1 flag (needs to be done, otherwise we will have multiples lcs_ticket for same user ticket)
     * 2. Add lcs_ticket entry
     * Sample LCS response:
     *  {
     *       "error": {
     *           "message": "There are already used tokens",
     *           "usedTokens": {
     *               "436082014-9": "967edc80-1b9a-4778-9bfd-93acb4553c7a"
     *           }
     *       }
     *    }
     */
    private function handleAlreadyPurchasedTicket(): void
    {
        $responseToProcess = $this->lcsResponse['error']['usedTokens'];
        /** prepare list of tickets to fix [slip_id => uuid, slip_id => uuid] */
        $ticketsToProcess = [];
        foreach ($responseToProcess as $lcsToken => $uuid) {
            // lcsToken is in form of "wlTicketToken-wlSlipId"
            $currentSlipId = -1;
            $tokenAndSlipSet = explode('-', $lcsToken);
            foreach ($this->lcsRequestPayload['tickets'] as $whitelottoSlipId => $ticketData) {
                /**
                 * Sample data:
                 * {
                 *       "tickets": {
                 *           "1": { // This is slipId, not ticketId
                 *               "token": "754881817-1",
                 *               "amount": "3.60",
                 *               "ip": "51.77.244.72",
                 *               "lines": {}
                 *           }
                 *       }
                 *   }
                 */
                $ticketHasBeenPurchased = $ticketData['token'] === $lcsToken;
                if ($ticketHasBeenPurchased) {
                    $currentSlipId = $whitelottoSlipId;
                    break;
                }
            }
            $ticketsToProcess[] = [
                'slipId' => $currentSlipId,
                'token' => $tokenAndSlipSet[0],
                'uuid' => $uuid,
            ];
        }

        $slipIds = array_column($ticketsToProcess, 'slipId');
        // We want to set tickets as processed only if has single line or rest lines have been already processed
        /** @var Database_Query_Builder $ticketQuery */
        $ticketQuery = DB::select(
            ['wut.id', 'id'],
            DB::expr('COUNT(DISTINCT wutl.whitelabel_user_ticket_slip_id) AS slips_count'),
            DB::expr('COUNT(DISTINCT lt.id) AS bought_slips_count'),
        )
            ->from([WhitelabelUserTicket::get_table_name(), 'wut'])
            ->join(['whitelabel_user_ticket_line', 'wutl'], 'LEFT')
            ->on('wutl.whitelabel_user_ticket_id', '=', 'wut.id')
            ->join(['lcs_ticket', 'lt'], 'LEFT')
            ->on('lt.whitelabel_user_ticket_slip_id', '=', 'wutl.whitelabel_user_ticket_slip_id')
            ->where('wutl.whitelabel_user_ticket_slip_id', 'IN', $slipIds)
            ->group_by('wut.id')
            ->having('slips_count', '=', 1)
            ->or_having('bought_slips_count', '=', DB::expr('slips_count - 1'))
            ->execute();

        $ticketIds = array_column($ticketQuery->as_array(), 'id');
        DB::update(WhitelabelUserTicket::get_table_name())
            ->set([
                'is_synchronized' => true
            ])
            ->where('id', 'IN', $ticketIds)
            ->execute();

        // prepare an insert statement
        $query = DB::insert('lcs_ticket');

        // Set the columns
        $query->columns(
            [
                'whitelabel_user_ticket_slip_id',
                'uuid',
            ]
        );

        foreach ($ticketsToProcess as $ticket) {
            $query->values(
                [
                    $ticket['slipId'],
                    $ticket['uuid'],
                ]
            );
        }

        $query->execute();
    }
}
