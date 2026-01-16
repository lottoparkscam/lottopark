<?php

namespace Fuel\Tasks;

set_time_limit(15 * 60);
ini_set('memory_limit', '1024M');

use Container;
use Fuel\Core\Cli;
use Fuel\Core\Fuel;
use Services_Raffle_Logger;
use Services_Raffle_Sync_Ticket;
use const PHP_EOL;

class Sync_Tickets
{
    private Services_Raffle_Sync_Ticket $ticket_synchronizer;
    private Services_Raffle_Logger $raffle_logger;

    public function __construct()
    {
        $this->ticket_synchronizer = Container::get(Services_Raffle_Sync_Ticket::class);
        $this->raffle_logger = Container::get(Services_Raffle_Logger::class);
    }

    public function help(): void
    {
        echo 'Commands:' . PHP_EOL .
            '1. php oil r sync_tickets raffle_slug=gg-world-raffle type=open|closed' . PHP_EOL .
            '   Command synchronizes LCS tickets and generated prizes for users';
    }

    public function run(string $raffle_slug, string $raffle_type = 'closed'): void
    {
        Cli::write(sprintf('Starting tickets synchronization (env <%s>) ...', Fuel::$env));

        $this->raffle_logger->subscribe(function (string $message) {
            Cli::write($message);
        });
        $this->ticket_synchronizer->synchronize($raffle_slug, $raffle_type);

        Cli::write('Task finished successfully!' . PHP_EOL);
    }
}
