<?php

namespace Fuel\Tasks;

use Container;
use Fuel\Core\Cli;
use Services\Api\Slots\IntegrationService;
use Throwable;

final class Slots
{
    private IntegrationService $integrationService;

    public function __construct()
    {
        $this->integrationService = Container::get(IntegrationService::class);
    }

    public function addNewProviderForWhitelabel(string $whitelabelTheme, string $slotProviderSlug): void
    {
        if (empty($_ENV['WP_SEEDER_TOKEN'])) {
            Cli::write('You have to fill WP_SEEDER_TOKEN in .env to be able to use seeders.');
            die;
        }

        try {
            $this->integrationService->addWhitelabelSlotProvider($whitelabelTheme, $slotProviderSlug);
            Cli::write('WhitelabelSlotProvider added.');
        } catch (Throwable $throwable) {
            Cli::write($throwable->getMessage());
        }

        $this->integrationService->runWordpressSeeders();

        Cli::write('Remember to: fill in credentials, enable WhitelabelSlotProvider');
    }
}
