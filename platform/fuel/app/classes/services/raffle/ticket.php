<?php

use Modules\Account\Balance\BonusBalance;
use Wrappers\Db;
use Models\Raffle;
use Models\WhitelabelUser;
use Services\Shared\System;
use Models\WhitelabelUserBonus;
use Models\WhitelabelRaffleTicket;
use Repositories\Orm\RaffleRepository;
use GuzzleHttp\Exception\ClientException;
use Modules\Account\Balance\BalanceContract;
use Modules\Account\Balance\InteractsWithBalance;
use Modules\Account\Balance\BalanceStrategyResolver;
use Services\Logs\FileLoggerService;
use Services_Lcs_Raffle_Buy_Ticket_Contract as BuyTicketApi;
use Services_Lcs_Raffle_Ticket_Taken_Contract as TakenTicketsApi;

class Services_Raffle_Ticket implements InteractsWithBalance
{
    private Services_Lcs_Raffle_Buy_Ticket_Contract $buy_ticket_api;
    private Services_Lcs_Raffle_Ticket_Taken_Contract $taken_tickets_api;
    private WhitelabelUser $user_dao;
    private Raffle $raffle_dao;
    private WhitelabelRaffleTicket $ticket_dao;
    private Services_Raffle_Token_Ticket_Resolver $ticket_token_resolver;
    private Services_Currency_Calc $currency_calc;
    private Services_Raffle_Factory_Ticket $ticket_factory;
    private Services_Raffle_Number_Validator $number_validator;
    private Db $db;
    private BalanceContract $balance_strategy;
    private BalanceStrategyResolver $balance_resolver;
    private System $system;
    private RaffleRepository $raffleRepo;
    private FileLoggerService $fileLoggerService;
    private ?WhitelabelUserBonus $userBonus = null;

    public function __construct(
        TakenTicketsApi $taken_tickets_api,
        BuyTicketApi $buy_ticket_api,
        WhitelabelUser $user,
        Raffle $raffle,
        WhitelabelRaffleTicket $ticket,
        Services_Raffle_Token_Ticket_Resolver $ticket_token_resolver,
        Services_Raffle_Factory_Ticket $ticket_factory,
        Services_Raffle_Number_Validator $number_validator,
        Services_Currency_Calc $currency_calc,
        Db $db,
        System $system,
        BalanceStrategyResolver $balance_resolver,
        RaffleRepository $raffleRepo,
        FileLoggerService $fileLoggerService
    ) {
        $this->buy_ticket_api = $buy_ticket_api;
        $this->taken_tickets_api = $taken_tickets_api;
        $this->user_dao = $user;
        $this->raffle_dao = $raffle;
        $this->ticket_dao = $ticket;
        $this->ticket_token_resolver = $ticket_token_resolver;
        $this->currency_calc = $currency_calc;
        $this->ticket_factory = $ticket_factory;
        $this->number_validator = $number_validator;
        $this->db = $db;
        $this->balance_resolver = $balance_resolver;
        $this->system = $system;
        $this->raffleRepo = $raffleRepo;
        $this->fileLoggerService = $fileLoggerService;
    }

    public function purchase(
        int $whitelabel_id,
        string $raffle_slug,
        string $type,
        array $ticket_numbers,
        int $user_id
    ): WhitelabelRaffleTicket {
        $raffle = $this->get_and_verify_raffle($raffle_slug);
        $user = $this->getAndVerifyUser($user_id);
        $ticket_numbers = $this->verify_numbers($raffle, $ticket_numbers);
        $this->determinePaymentMethod_and_verify_balance($raffle, $user, $ticket_numbers);
        $this->verify_ticket_can_be_bought_on_lcs($raffle->slug, $ticket_numbers, $type);
        return $this->buy_ticket_on_lcs_and_create_models($whitelabel_id, $raffle, $user, $ticket_numbers);
    }

    private function get_and_verify_raffle(string $slug): Raffle
    {
        $raffle = $this->raffle_dao->get_by_slug_with_currency_and_rule($slug);

        if ($raffle->is_sell_temporary_disabled) {
            $dates = $raffle->sell_open_dates_objects;
            $date = reset($dates);
            throw new BadMethodCallException(sprintf('Ticket purchase is closed until %s', $date->format('Y-m-d H:i')));
        }

        if (!$raffle->is_sell_enabled) {
            throw new BadMethodCallException('Lottery is temporary not playable due prizes calculation.');
        }

        return $raffle;
    }

    private function getAndVerifyUser(int $user_id): WhitelabelUser
    {
        return $this->user_dao->get_user_by_id($user_id);
    }

    private function determinePaymentMethod_and_verify_balance(Raffle $raffle, WhitelabelUser $user, array $ticket_numbers): void
    {
        $required_amount = $this->calculate_ticket_amount($raffle, $user, $ticket_numbers);
        $required_amount_in_user_currency = $this->currency_calc->convert_to_any(
            $required_amount,
            $raffle->getFirstRule()->currency->code,
            $user->currency->code
        );

        if ($this->userBonus) {
            $this->balance_resolver->addUserBonus($this->userBonus);
        }

        $this->balance_resolver->determinePaymentMethod($this, $raffle, $user, $required_amount_in_user_currency);

        $this->verify_user_hasSufficientBalanceToProcess($user, $required_amount_in_user_currency);
    }

    private function calculate_ticket_amount(Raffle $raffle, WhitelabelUser $user, array $ticket_line_numbers): float
    {
        if (
            $this->userBonus
            && $this->userBonus->isFreeTicketRaffleAvailableForUser((int) $user->id)) {

            return 0.00;
        }

        return ($raffle->getFirstRule()->line_price + $raffle->getFirstRule()->fee) * sizeof($ticket_line_numbers);
    }

    private function calculateFeeAmount(Raffle $raffle, WhitelabelUser $user, array $ticket_line_numbers): float
    {
        if (
            $this->userBonus
            && $this->userBonus->isFreeTicketRaffleAvailableForUser((int) $user->id)) {

            return 0.00;
        }

        return $raffle->getFirstRule()->fee * count($ticket_line_numbers);
    }

    private function verify_ticket_can_be_bought_on_lcs(string $raffle_slug, array $ticket_line_numbers, string $raffle_type = 'closed'): void
    {
        $response = $this->taken_tickets_api->request($raffle_slug, $raffle_type);
        $taken_numbers = $response->get_data();
        $duplicated_tickets = [];
        foreach ($ticket_line_numbers as $ticket_no) {
            if (in_array($ticket_no, $taken_numbers)) {
                $duplicated_tickets[] = $ticket_no;
            }
        }
        if (!empty($duplicated_tickets)) {
            throw new BadMethodCallException(sprintf(
                _('Given numbers <%s> has been purchased by someone else. Please select new numbers.'),
                implode(', ', $duplicated_tickets)
            ));
        }
    }

    private function buy_ticket_on_lcs_and_create_models(int $whitelabel_id, Raffle $raffle, WhitelabelUser $user, array $ticket_numbers): WhitelabelRaffleTicket
    {
        $amount_with_fee = $this->calculate_ticket_amount($raffle, $user, $ticket_numbers);
        $amount_without_fee = $amount_with_fee - $this->calculateFeeAmount($raffle, $user, $ticket_numbers);
        try {
            $response = $this->buy_ticket_api->request([
                'tickets' => [
                    [
                        'token' => $this->ticket_token_resolver->issue($whitelabel_id),
                        'amount' => $amount_without_fee,
                        'ip' => $user->last_ip,

                        /**
                         * We have recorded a few cases with the wrong 'lines' format.
                         * Instead of the 'lines' array it contained the object 'lines'.
                         * Then Index '1' of the first element was causing an error in the LCS.
                         *
                         * Valid format:    [{"numbers":[[1]]},{"numbers":[[2]]}]
                         * Invalid format:  {1:{"numbers":[[1]]},"2":{"numbers":[[2]]}}
                         *
                         * https://ggintsoftware.slack.com/archives/GHCDA1VKK/p1663838544396849
                         */
                        'lines' => array_map(function (int $number) {
                            return ['numbers' => [[$number]]];
                        }, $ticket_numbers)
                    ]
                ]
            ], $raffle->slug);
        } catch (ClientException $e) {
            if ($e->getCode() === 403) {
                $json = json_decode($e->getResponse()->getBody()->getContents(), true);
                throw new BadMethodCallException($json['error']);
            }
            throw $e;
        }
        $response_body = $response->get_body();
        $lcs_ticket = reset($response_body['lottery_tickets']);

        $this->db->start_transaction();
        try {
            $ticket = $this->ticket_factory->create_from_lcs_ticket_data(
                $whitelabel_id,
                $lcs_ticket,
                $raffle,
                $user,
                $ticket_numbers,
                $this->balance_strategy
            );

            $this->ticket_dao->store($ticket);

            $this->balance_strategy->debitByTicket($ticket);
            if ($this->balance_strategy->is_enqueued()) {
                $this->balance_strategy->dispatch();
            }
        } catch (Throwable $exception) {
            $this->db->rollback_transaction();
            $this->fileLoggerService->error($exception->getMessage());
            throw $exception;
        }
        $this->db->commit_transaction();

        $this->raffleRepo->resetDrawLinesAndDisableSellWhenSoldOut($raffle->id);

        return $ticket;
    }

    private function verify_numbers(Raffle $raffle, array $ticket_numbers): array
    {
        return $this->number_validator->validate($raffle, $ticket_numbers);
    }

    public function addUserBonus(WhitelabelUserBonus $bonus): void
    {
        $this->userBonus = $bonus;
    }

    public function setBalanceStrategy(BalanceContract $balance): void
    {
        $this->balance_strategy = $balance;
    }

    private function verify_user_hasSufficientBalanceToProcess(WhitelabelUser $user, float $required_amount_in_user_currency): void
    {
        if ($this->balance_strategy->hasSufficientBalanceToProcessSingular($user, $required_amount_in_user_currency)) {
            return;
        }

        $message = _('Your balance is too low to proceed. Please make <a href="/deposit/">a deposit</a>');
        $isBonusBalance = $this->balance_strategy->source() === BonusBalance::COLUMN_NAME;
        if ($isBonusBalance) {
            $message = _("Your bonus balance is too low to proceed.");
        }

        throw new BadMethodCallException($message);
    }
}
