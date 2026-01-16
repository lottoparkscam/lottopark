<?php

namespace Fuel\Tasks\Seeders;

use Container;
use Exception;
use Modules\Payments\PaymentFacadeContract;
use const PHP_EOL;

final class Jeton extends Seeder
{
    private PaymentFacadeContract $facade;
    private int $id;
    private string $name;
    private array $supported;

    public function execute(): void
    {
        try {
            $this->facade = Container::getPaymentFacade('jeton');
            $config = $this->facade->getConfig();
            $this->id = $config['id'];
            $this->name = $config['name'];
            $this->supported = $config['supported_currency'];
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            echo 'Unable to initialize Jeton seeder, due errors. No data has been seeded.';
            return;
        }
        parent::execute();
    }

    protected function columnsStaging(): array
    {
        return [
            'payment_method' => [
                'id',
                'name'
            ],
            'payment_method_supported_currency' => [
                'payment_method_id',
                'code'
            ]
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'payment_method' => [
                [$this->id, $this->name],
            ],
            'payment_method_supported_currency' => array_map(fn (string $currency) => [$this->id, $currency], $this->supported)
        ];
    }
}
