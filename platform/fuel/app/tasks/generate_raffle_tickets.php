<?php

declare(strict_types=1);

namespace Fuel\Tasks;

use Task_Cli;
use Container;
use Models\WhitelabelUser;
use Fuel\Core\Cli;
use Services_Raffle_Ticket;
use Repositories\Orm\RaffleRepository;
use Services_Lcs_Raffle_Ticket_Free_Contract;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\Raffle;

final class Generate_Raffle_Tickets extends Task_Cli
{
    public const WHITELABEL_ID = 1;
    public const LINES_PER_TICKET = 50;
    public const RAFFLE_TYPE = 'closed';
    private RaffleRepository $raffleRepository;
    private Raffle $raffle;

    public function help(): void
    {
        echo 'Commands:' . PHP_EOL .
            'WARNING: You must have any user in this whitelabel.' . PHP_EOL .
            '1. php oil r generate_raffle_tickets gg-world-raffle 50' . PHP_EOL .
            'Example: Check 50 tickets gg-world-raffle. Optional can add raffle_type' . PHP_EOL .
            '2. php oil r generate_raffle_tickets gg-world-raffle 50 true' . PHP_EOL .
            'Example: Check 50 tickets gg-world-raffle with bonus balance';
    }

    public function run(
        string $raffleSlug,
        string $tickets,
        bool $bonus = false,
        string $raffleType = self::RAFFLE_TYPE
    ): void {
        $this->raffleRepository = Container::get(RaffleRepository::class);
        $this->raffle = $this->raffleRepository->findBy('slug', $raffleSlug);
        if ($bonus) {
            $this->raffle->whitelabelRaffle->isBonusBalanceInUse = true;
            $message = Cli::color('Tickets from bonus balance', 'green');
        } else {
            $this->raffle->whitelabelRaffle->isBonusBalanceInUse = false;
            $message = Cli::color('Tickets from user balance', 'green');
        }
        echo $message . PHP_EOL;

        $user = $this->getUser();
        $rest = $tickets%self::LINES_PER_TICKET;
        $iterations = $tickets/self::LINES_PER_TICKET;
        for ($i = 1; $i <= $iterations; $i++) {
            $this->buyRandomRaffleTickets(
                self::LINES_PER_TICKET,
                $raffleSlug,
                $raffleType,
                $user
            );
        }

        if ($rest!=0) {
            $this->buyRandomRaffleTickets(
                $rest,
                $raffleSlug,
                $raffleType,
                $user
            );
        }
    }

    private function getUser(): WhitelabelUser
    {
        $user = WhitelabelUser::dao()->push_criterias([
            new Model_Orm_Criteria_Where('is_active', true)
        ])->get_one();
        $user->balance = 100000;
        $user->bonus_balance = 100000;
        $user->save();
        return $user;
    }

    private function getRandomAvailableNumbers(
        int $tickets,
        string $raffleSlug,
        string $raffleType = self::RAFFLE_TYPE,
    ): array {
        /** @var Services_Lcs_Raffle_Ticket_Free_Contract $result */
        $result = Container::get(Services_Lcs_Raffle_Ticket_Free_Contract::class)->request($raffleSlug, $raffleType)->get_data();
        shuffle($result);
        return array_slice($result, 0, $tickets);
    }

    private function buyRandomRaffleTickets(
        int $tickets,
        string $raffleSlug,
        string $raffleType,
        WhitelabelUser $user
    ): void {
        /** @var Services_Raffle_Ticket $service */
        $service = Container::get(Services_Raffle_Ticket::class);
        $requestedLines = $this->getRandomAvailableNumbers($tickets, $raffleSlug, $raffleType);
        $service->purchase(
            self::WHITELABEL_ID,
            $raffleSlug,
            $raffleType,
            $requestedLines,
            $user->id
        );
    }
}
