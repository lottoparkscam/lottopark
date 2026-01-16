<?php

namespace Fuel\Tasks;

use Fuel\Core\Fuel;
use Services\Api\Slots\Providers\SlotegratorApiService;
use Container;
use Task_Cli;

final class Slotegrator_Self_Check extends Task_Cli
{
    private SlotegratorApiService $slotegratorApiService;

    public function __construct()
    {
        $this->disableOnProduction();

        $this->slotegratorApiService = Container::get(SlotegratorApiService::class);
    }

    public function run()
    {
        $this->sendSelfValidateRequest();
    }

    private function isProdEnv(): bool
    {
        return Fuel::$env === Fuel::PRODUCTION;
    }

    private function sendSelfValidateRequest(): void
    {
        $response = $this->slotegratorApiService->sendRequest('/self-validate', []);
        /** We have to change bool to 0/1 in order to display it in cli */
        $response['success'] = $response['success'] ? 1 : 0;
        print_r($response);
    }
}
