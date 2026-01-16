<?php

namespace Fuel\Tasks;

use Container;
use Fuel\Core\Cli;
use Fuel\Core\Fuel;
use Modules\Payments\Synchronizer\PaymentsCleaner;
use Services\Shared\Logger\InMemoryLogger;
use const PHP_EOL;

class Set_Old_Pending_Transactions_As_Fail
{
    private InMemoryLogger $inMemoryLogger;
    private PaymentsCleaner $paymentsCleaner;

    public function __construct()
    {
        $this->inMemoryLogger = Container::get(InMemoryLogger::class);
        $this->paymentsCleaner = Container::get(PaymentsCleaner::class);
    }

    public function help(): void
    {
        Cli::write([
            'Commands:',
            '   php oil r clean_payments -output',
            '   Set out dated pending payments status to failed',
        ]);
    }

    public function run(): void
    {
        Cli::write(sprintf('Checking payments (env <%s>) ... %s', Fuel::$env, PHP_EOL));

        if (Cli::option('output')) {
            $this->inMemoryLogger->subscribe(function (string $message) {
                Cli::write($message);
            });
        }

        $this->paymentsCleaner->synchronize();

        Cli::write(PHP_EOL . 'Task finished successfully!');
    }
}
