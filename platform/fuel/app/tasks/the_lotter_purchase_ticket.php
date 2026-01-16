<?php

namespace Fuel\Tasks;

use Container;
use Core\App;
use Email\Email;
use Exception;
use Fuel\Core\Config;
use Fuel\Core\DB;
use Fuel\Core\Package;
use Lotto_Helper;
use Services\Logs\FileLoggerService;
use Services\LotteryProvider\TheLotterPurchaseTicketService;

final class the_lotter_purchase_ticket
{
    private TheLotterPurchaseTicketService $lotterPurchaseTicketService;
    private FileLoggerService $logger;
    private App $app;

    public function __construct()
    {
        $this->lotterPurchaseTicketService = Container::get(TheLotterPurchaseTicketService::class);
        $this->logger = Container::get(FileLoggerService::class);
        $this->app = Container::get(App::class);
    }

    public function purchase(): void
    {
        if (!$this->app->isProduction()) {
            return;
        }

        set_time_limit(0);

        Config::load('platform', true);

        $this->lotterPurchaseTicketService->purchaseTickets();
    }

    private function lockPurchaseTask(): bool
    {
        $updatedRows = DB::update('setting')
            ->set(['value' => 1])
            ->where('name', 'task_lockpurchase')
            ->and_where('value', 0)
            ->execute();

        if ($updatedRows < 1) {
            $this->logger->error(
                'TheLotter purchase is locked. Another task is running. If it happens again, check why it is locked.'
            );
            return false;
        }

        return true;
    }

    private function sendPurchaseTicketLockedEmail(): void
    {
        Config::load('lotteries', true);
        $recipients = Config::get('lotteries.emergency_emails');
        Package::load('email');
        $email = Email::forge();
        $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
        $email->to($recipients);
        $title = "Lotto Emergency: The purchase ticket job has been locked.";
        $email->subject($title);

        $body_text = "It seems that the purchase ticket job runs over 30 seconds, " .
            "so the script couldn't start next purchase round. " .
            "If the e-mail is received every 30 seconds - it means serious " .
            "problem as tickets are not purchased! The solution: manually " .
            "clear up the flag by running out following query: " .
            "\"UPDATE setting SET value = 0 WHERE name = `task_lockpurchase`\" " .
            "and remove the `/home/site/www/platform/fuel/app/cache/model_setting` " .
            "folder [task.php/purchasetickets].";

        $email->body($body_text);

        try {
            $email->send();
        } catch (Exception $e) {
            $errorMessage = "There is a problem with delivering the mail. " .
                "Description of error: " . $e->getMessage();
            $this->logger->error(
                $errorMessage
            );
        }
    }
}
