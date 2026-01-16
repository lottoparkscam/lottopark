<?php

namespace Fuel\Tasks;

use Container;
use Exception;
use Fuel\Core\Cli;
use Fuel\Core\DB;
use Fuel\Tasks\Receipts\Receipts_Testing;
use InvalidArgumentException;
use Receipts_Contract;
use Receipts_Provider;
use Task_Cli;

class Receipt extends Task_Cli
{
    /** @var Receipts_Provider */
    private $provider;

    public function __construct()
    {
        $this->disableOnProduction();

        $this->provider = Container::get(Receipts_Provider::class);
    }

    public function help(): void
    {
        Cli::write([
            'Commands:',
            '   php oil r receipts',
            '   1. php oil r ?test=1',
            '   Command runs all receipts, if -test option passed, also test Receipts will be executed.',
        ]);
    }

    public function run(?string $receipt = null): void
    {
        $withTests = Cli::option('test', false);
        $this->write(sprintf('Running receipts %s tests...', $withTests ? 'with' : 'without'), true, true);

        $receipts = $this->provider->get_receipts();

        if (!empty($receipt)) {
            $receipts = array_filter($receipts, function (string $receipt_class_name) use (&$receipt) {
                return $receipt_class_name === $receipt;
            });
        }

        if (empty($receipts)) {
            throw new InvalidArgumentException(sprintf('Receipt %s not found.', $receipt));
        }

        foreach ($receipts as $receiptClassName) {
            DB::start_transaction();
            try {
                /** @var Receipts_Contract $receipt */
                $receipt = Container::get($receiptClassName);
                if (!$withTests && $receipt instanceof Receipts_Testing) {
                    continue;
                }
                $receipt->__invoke($this);
            } catch (Exception $exception) {
                DB::rollback_transaction();
                $this->write('ROLLBACK..');
                $receipt->rollBack($this);
                throw $exception;
            }
            DB::commit_transaction();
        }
    }

    public function rollback(string $receipt): void
    {
        $this->write(sprintf('Rollback receipt %s...', $receipt), true);

        $receipts = array_filter($this->provider->get_receipts(), function (string $receipt_class_name) use (&$receipt) {
            return $receipt_class_name === $receipt;
        });

        if (empty($receipts)) {
            throw new InvalidArgumentException(sprintf('Receipt %s not found.', $receipt));
        }

        DB::start_transaction();
        try {
            /** @var Receipts_Contract $receipt */
            $receipt = Container::get($receipt);
            $receipt->rollBack($this);
        } catch (Exception $exception) {
            DB::rollback_transaction();
            $this->write('ROLLBACK..');
            throw $exception;
        }
        DB::commit_transaction();
    }

    public function refresh(string $receipt): void
    {
        $this->write(sprintf('Refreshing receipt %s...', $receipt), true);

        $receipts = array_filter($this->provider->get_receipts(), function (string $receipt_class_name) use (&$receipt) {
            return $receipt_class_name === $receipt;
        });

        if (empty($receipts)) {
            throw new InvalidArgumentException(sprintf('Receipt %s not found.', $receipt));
        }

        /** @var Receipts_Contract $receipt */
        $receipt = Container::get($receipt);

        $receipt->rollBack($this);

        DB::start_transaction();
        try {
            $receipt->__invoke($this);
        } catch (Exception $exception) {
            DB::rollback_transaction();
            throw $exception;
        }
        DB::commit_transaction();
    }

    public function write(string $message, bool $new_line = false, bool $divider = false): void
    {
        CLI::write($message);
        if ($divider) {
            CLI::write('--------------------------------------------');
        }
        if ($new_line) {
            CLI::write('');
        }
    }

    public function writeStep(string $message): void
    {
        CLI::write('  ' . $message);
    }
}
