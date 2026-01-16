<?php

use Helpers\UserHelper;
use Models\Whitelabel;
use Modules\Mediacle\UserRegisterEventHandler;
use Services\Logs\FileLoggerService;
use Services\Plugin\TransactionService;

class Events_User_Transaction_Purchase_Success extends Events_Event
{
    public static function handle(array $data): void
    {
        self::run($data);
    }

    protected static function run(array $data): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

          /** Removed direct Facebook Pixel call; the event is now sent via GTM */
//        Forms_Wordpress_Pixels_Gtag::trigger_event("purchase", $data["plugin_data"]);
//        Forms_Wordpress_Pixels_Facebook::trigger_event("Purchase", $data["plugin_data"]);

        $whitelabelTheme = $data['whitelabel_theme'];
        switch ($whitelabelTheme) {
            case Whitelabel::DOUBLEJACK_THEME:
                Container::get(UserRegisterEventHandler::class)->handle($data);
                break;
            case Whitelabel::LOTTOPARK_THEME:
                $user = userHelper::getUser();
                try {
                    /** @var TransactionService $transactionService */
                    $transactionService = Container::get(TransactionService::class);
                    $transactionService->sendToTrafficBarClickIdAndCommission($user);
                    $transactionService->sendCommissionToPrimeadsByUser($user);
                } catch (Throwable $exception) {
                    $fileLoggerService->error(
                        'Message: ' . $exception->getMessage() .
                        'File: ' . $exception->getFile() .
                        'Line: ' . $exception->getLine()
                    );
                }
                break;
        }
    }
}
