<?php

namespace Fuel\Tasks;

use Container;
use Fuel\Core\Cli;
use Fuel\Core\Fuel;
use Modules\Payments\Synchronizer\PaymentsSynchronizer;
use Services\Shared\Logger\InMemoryLogger;

use const PHP_EOL;

class Sync_Payments
{
    private InMemoryLogger $inMemoryLogger;
    private PaymentsSynchronizer $syncService;

    public function __construct()
    {
        $this->inMemoryLogger = Container::get(InMemoryLogger::class);
        $this->syncService = Container::get(PaymentsSynchronizer::class);
    }

    public function help(): void
    {
        Cli::write([
            'Commands:',
            '   php oil r sync_payments -output',
            '   Command synchronizes pending payments by checking transaction status on third party provider api',
            '   Add -output option to see output in terminal (task wont save data in DB then!)',
        ]);
    }

    public function run(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        Cli::write(sprintf('Starting payments synchronization (env <%s>) ... %s', Fuel::$env, PHP_EOL));

        if (Cli::option('output')) {
            $this->inMemoryLogger->subscribe(function (string $message) {
                Cli::write($message);
            });
        }

        $this->syncService->synchronize();

        Cli::write(PHP_EOL . 'Task finished successfully!');
    }
}
