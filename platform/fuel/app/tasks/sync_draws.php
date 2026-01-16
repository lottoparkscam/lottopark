<?php

namespace Fuel\Tasks;

use Container;
use Fuel\Core\Cli;
use Fuel\Core\Fuel;
use Helpers_App;
use Models\Whitelabel;
use Services_Raffle_Logger;
use Services_Raffle_Sync_Draw;
use Services_Raffle_Sync_Ticket;

use const PHP_EOL;

class Sync_Draws
{
    private Services_Raffle_Sync_Draw $draw_synchronizer;
    private Services_Raffle_Sync_Ticket $ticket_synchronizer;
    private Services_Raffle_Logger $raffle_logger;

    public function __construct()
    {
        $this->draw_synchronizer = Container::get(Services_Raffle_Sync_Draw::class);
        $this->raffle_logger = Container::get(Services_Raffle_Logger::class);
        $this->ticket_synchronizer = Container::get(Services_Raffle_Sync_Ticket::class);
    }

    public function help(): void
    {
        Cli::write([
            'Commands:',
            '   php oil r sync_draws',
            '   1. php oil r sync_draws raffle_slug=gg-world-raffle -force',
            '   Command synchronizes LCS draws, prizes and tiers from LCS with WL instance & invokes tickets synchronization task when tickets = 1',
        ]);
    }

    // TODO: {Vordis 2020-12-22 15:46:26} hotfix for prepaid this shall be reforged into full fledged feature with non hardcoded values
    private function is_not_eligible_for_payout(string $raffle_slug): bool
    {
        if ($raffle_slug !== 'faireum-raffle' || Helpers_App::is_not_production_environment()) {
            return false;
        }

        $whitelabel = Whitelabel::find('first', [
            'where' => [
                'theme' => 'faireum'
            ]
        ]);
        $has_insufficient_prepaid_for_payout = $whitelabel->prepaid < 907.60;

        return $has_insufficient_prepaid_for_payout;
    }

    public function run(string $raffle_slug): void
    {
        $force = Cli::option('force', false);

        set_time_limit(15 * 60);
        ini_set('memory_limit', '256M');

        if ($this->is_not_eligible_for_payout($raffle_slug)) {
            mail('peter@whitelotto.com', 'Faireum has insufficient prepaid for raffle', 'Faireum has insufficient prepaid for raffle');

            return;
        }

        Cli::write(sprintf('Starting draw synchronization (env <%s>) ...', Fuel::$env));

        $this->raffle_logger->subscribe(function (string $message) {
            Cli::write($message);
        });
        $this->draw_synchronizer->synchronize($raffle_slug, $force);
        $this->ticket_synchronizer->synchronize($raffle_slug, $force);

        Cli::write('Task finished successfully!' . PHP_EOL);
    }
}
