<?php

namespace Fuel\Tasks;

set_time_limit(600);
ini_set('memory_limit', '1024M');

use Container;
use Fuel\Core\Cli;
use InvalidArgumentException;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\Raffle;
use Models\WhitelabelUser;
use Modules\Account\Balance\BalanceContract;
use Services_Lcs_Raffle_Ticket_Free_Contract as FreeTicketsApi;
use Services_Raffle_Ticket;
use Task_Cli;

final class Generate extends Task_Cli
{
    private const WHITELABEL_ID = 1; # todo: st hardcoded
    private const RAFFLE_OPEN_TYPE = 'open';
    private const RAFFLE_CLOSED_TYPE = 'closed';

    private FreeTicketsApi $free_tickets_api;
    private Services_Raffle_Ticket $purchase_ticket;
    private BalanceContract $user_balance;
    private WhitelabelUser $user_dao;
    private Raffle $raffle_dao;

    public function __construct()
    {
        $this->disableOnProduction();

        $this->free_tickets_api = Container::get(FreeTicketsApi::class);
        $this->purchase_ticket = Container::forge()->get(Services_Raffle_Ticket::class);
        $this->user_balance = Container::forge()->get(BalanceContract::class);
        $this->user_dao = Container::forge()->get(WhitelabelUser::class);
        $this->raffle_dao = Container::forge()->get(Raffle::class);
    }

    public function help(): void
    {
        Cli::write([
            'Commands:',
            '      php oil r generate raffle-slug',
            '   1. php oil r generate type=open|closed ?lines=1000 ?email=null',
            '   3. If user is not provided random user will be used. Lines count is max amount of generated.',
        ]);
    }

    public function run(string $raffle_slug, string $raffle_type = self::RAFFLE_CLOSED_TYPE): void
    {
        $raffle = $this->get_raffle($raffle_slug);
        $lines_count = Cli::option('lines', $raffle->max_bets);
        $user_email = Cli::option('email');
        $this->verify_inputs($raffle, $raffle_type, $lines_count);

        Cli::write(sprintf('Generating %s numbers for raffle %s for %s user', $lines_count, $raffle_slug . ' - ' . $raffle_type, $user_email ?? 'random'));

        $generated_ticket_count = 0;

        for (; ;) {
            $available_numbers = $this->get_available_ticket_numbers_for_raffle($raffle_slug, $raffle_type);
            $available_numbers_count = sizeof($available_numbers);

            if ($lines_count >= $raffle->getFirstRule()->max_lines_per_draw) {
                $lines_count = $available_numbers_count;
            }

            if (empty($available_numbers)) {
                Cli::error('There is no more free numbers to generate');
                break;
            }

            $slice = array_slice($available_numbers, 0, rand(1, $lines_count));
            $generated_ticket_count += count($slice);

            if (empty($slice)) {
                break;
            }
            $user = $this->get_user($user_email);
            $this->recharge_user_account($user);

            Cli::write(sprintf('Tickets for user <%s>: %d-%d = %s', $user->email, reset($slice), end($slice), implode(', ', $slice)));

            $this->purchase_ticket->purchase(self::WHITELABEL_ID, $raffle_slug, $raffle_type, $slice, $user->id);
        }
    }

    /**
     * @param string|null $email
     *
     * @return WhitelabelUser - if email provided, user will be returned otherwise random one
     */
    private function get_user(?string $email = null): WhitelabelUser
    {
        $this->user_dao->push_criteria(new Model_Orm_Criteria_Where('is_active', true));
        if ($email) {
            $this->user_dao->push_criteria(new Model_Orm_Criteria_Where('email', $email));
            return $this->user_dao->get_one();
        }

        $users = $this->user_dao->get_results(5);
        if (empty($users)) {
            throw new InvalidArgumentException('No users found!');
        }
        return $users[array_rand($users)];
    }

    /**
     * We have to ensure user has sufficient account balance to buy tickets after each iteration.
     *
     * @param WhitelabelUser $user
     */
    private function recharge_user_account(WhitelabelUser $user): void
    {
        $amount_to_charge = 100000 - $user->balance;
        if ($amount_to_charge <= 0) {
            return;
        }
        $this->user_balance->increase($user->id, $amount_to_charge, $user->currency->code);
        $this->user_balance->dispatch();
    }

    private function get_available_ticket_numbers_for_raffle(string $raffle_slug, string $raffle_type): array
    {
        return $this->free_tickets_api->request($raffle_slug, $raffle_type)->get_data();
    }

    private function verify_inputs(Raffle $raffle, string $raffle_type, ?int $tickets_count = null): void
    {
        if (!in_array($raffle_type, [self::RAFFLE_OPEN_TYPE, self::RAFFLE_CLOSED_TYPE])) {
            throw new InvalidArgumentException('Invalid raffle type');
        }
        if ($tickets_count > $raffle->getFirstRule()->max_lines_per_draw) {
            throw new InvalidArgumentException(sprintf('Ticket count can not be greater than %d', $raffle->getFirstRule()->max_lines_per_draw));
        }
    }

    private function get_raffle(string $raffle_slug): Raffle
    {
        return $this->raffle_dao->get_by_slug_with_currency_and_rule($raffle_slug);
    }
}
