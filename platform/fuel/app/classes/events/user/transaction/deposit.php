<?php

use Helpers\UserHelper;
use Models\Whitelabel;
use Services\Logs\FileLoggerService;
use Services\Plugin\TransactionService;

class Events_User_Transaction_Deposit extends Events_Event
{
    public static function handle(array $data): void
    {
        $data['onCasinoEvent'] = true;
        parent::handle($data);
    }

    protected static function run(array $data): void
    {
        $data['onCasinoEvent'] = true;

        $fileLoggerService = Container::get(FileLoggerService::class);

        switch ($data['whitelabel_theme']) {
            case Whitelabel::LOTTOPARK_THEME:
                $user = UserHelper::getUser();
                try {
                    /** @var TransactionService $transactionService */
                    $transactionService = Container::get(TransactionService::class);

                    $isUserFirstDeposit = array_key_exists('last_deposit_status_manager', $data['plugin_data']) &&
                        array_key_exists('first_deposit_status_manager', $data['plugin_data']) && $data['plugin_data']['last_deposit_status_manager'] === 'Approved' && $data['plugin_data']['first_deposit_status_manager'] === 'Approved';
                    if (!empty($user) && $isUserFirstDeposit) {
                        $transactionService->sendToTagMarketingUserFirstDepositConversion($user);
                        $transactionService->sendToDigitalHubUserFirstDepositConversion($user);
                        $transactionService->sendToTibolarioFirstDepositConversion($user);
                        $transactionService->sendToLoudingAdsFirstDepositConversion($user);
                        $transactionService->sendToTagdFirstDepositConversion($user);
                    }
                } catch (Throwable $exception) {
                    $fileLoggerService->error($exception->getMessage());
                }
                break;
        }
        parent::run($data);
    }

    protected static function add_custom_data_to_plugins(array $data): array
    {
        $data['plugin_data']['last_balance_update'] = time();
        $data['plugin_data']['last_deposit_date'] = time();

        return $data;
    }
}
