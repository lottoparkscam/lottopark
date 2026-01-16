<?php

namespace Fuel\Tasks;

use Config_Lcs_Status;
use Container;
use Fuel\Core\Fuel;
use GO\Job;
use Helpers_App;
use Models\Whitelabel;
use Repositories\WhitelabelRepository;
use Services\Logs\FileLoggerService;
use Services\Plugin\MauticPluginImportService as MauticImport;
use Services_Scheduler;
use Task\Alert\AbstractAlertListener;
use Throwable;
use Wrappers\Decorators\ConfigContract;

final class Scheduler
{
    use Config_Lcs_Status;

    /** @var Services_Scheduler scheduler */
    private $scheduler;
    private $config;

    public function __construct()
    {
        $this->scheduler = Container::get(Services_Scheduler::class);
        $this->load_lcs_config();
        $this->config = Container::get(ConfigContract::class);
    }

    public function run(): void
    {
        $isSchedulerDisabled = !$this->config->get('scheduler.is_enabled');
        if ($isSchedulerDisabled) {
            return;
        }

        $this->add_lotteries_sync();
        $this->addSlotsSync();
        $this->add_gg_lotteries_sync();
        $this->addWpCron();
        $this->clearLogs();
        $this->saleReport();
        $this->logs();
        $this->deleteCarts();

        // Only Staging
        if (Fuel::$env === Fuel::STAGING) {
            $this->add_only_staging_tasks();
        }

        // Only Production
        if (Fuel::$env === Fuel::PRODUCTION) {
            $this->add_only_production_tasks();
        }

        $this->scheduler->run();
        $this->scheduler->check_failed_jobs();
    }

    private function logs(): void
    {
        if ($this->config->get('slack.syncLogs')) {
            $this->scheduler->add_task('sync_logs')->everyMinute();
        }
    }

    private function clearLogs()
    {
        $this->scheduler->add_task('clear_logs whitelabel_api_nonce')->everyMinute(30);
        $this->scheduler->add_task('clear_logs lottery_log')->daily(01);
        $this->scheduler->add_task('clear_logs lottorisq_log')->daily(01, 10);
        $this->scheduler->add_task('clear_logs payment_log')->daily(01, 20);
        $this->scheduler->add_task('clear_logs raffle_log')->daily(01, 30);
        $this->scheduler->add_task('clear_logs whitelabel_plugin_log')->daily(01, 40);
        $this->scheduler->add_task('clear_logs ip_login_try')->daily(01, 50);
        $this->scheduler->add_task('clear_logs slot_log')->daily(02);
        $this->scheduler->add_task('delete_old_logs')->daily(04, 30);
        $this->scheduler->add_task('clear_logs whitelabel_lottery_purchase_limit')->daily(00, 00);
    }

    private function deleteCarts()
    {
        $this->scheduler->add_task('delete_old_carts')->daily(00, 00);
    }

    private function saleReport()
    {
        $this->scheduler->add_task('report_sales')->daily(00, 20);
    }

    private function addSlotsSync()
    {
        $this->scheduler->add_task('slotegrator_sync_slot_games')->daily(04, 00);
    }

    private function add_lotteries_sync()
    {
        // every minute
        $this->scheduler->add_task('the_lotter_purchase_ticket:purchase')->everyMinute(5)->onlyOne();
        $this->scheduler->add_wget_empire_task('updateestimated')->everyMinute()->onlyOne();
        $this->scheduler->add_wget_empire_task('drawnotification')->everyMinute()->onlyOne();
        $this->scheduler->add_task('send_multi_draw_notifications')->everyMinute()->onlyOne();
        $this->scheduler->add_task('sync_payments')->everyMinute(15);
        $this->scheduler->add_task('lottery:update_keno_draw_data')->everyMinute()->onlyOne();

        // every x minutes
        $this->scheduler->add_wget_empire_task('providerscheck')->everyMinute(5);
        $this->scheduler->add_wget_empire_task('optimizeimages')->everyMinute(5);
        $this->scheduler->add_wget_main('wp-cron-multisite.php')->everyMinute(15);
        $this->scheduler->add_task('lottery:update_draw_data')->everyMinute(20);

        // every hour
        $this->scheduler->add_wget_empire_task('checkneteller')->hourly(5);
        $this->scheduler->add_task('set_old_pending_transactions_as_fail')->hourly();

        // every x hours
        $this->scheduler->add_task('check_lcs_pendings')->at(Services_Scheduler::everyXHours(2));

        // every day
        $this->scheduler->add_wget_empire_task('lottorisqbalance')->daily(00, 05);
        $this->scheduler->add_wget_empire_task('check_prepaids_are_low_for_whitelabels')->daily(00, 10);
//        $this->scheduler->add_wget_empire_task('check_superenalotto_delay')->daily(02, 00);

        // every month
        $this->scheduler->add_wget_empire_task('calcaffpayouts')->monthly('*', 01, 00, 00);
        $this->scheduler->add_wget_empire_task('monthlyvolume')->monthly('*', 01, 00, 00);
    }

    private function add_gg_lotteries_sync()
    {
        if (!$this->lcs_enabled) {
            return;
        }

        // Raffle
        $this->scheduler->add_task('ticket_purchase_opening_task');
        $this->scheduler->add_task('sync_draws_all_raffle')->onlyOne();

        // GGWorld sync
        $this->scheduler->add_task('lcs:update_draw_data gg-world')->onlyOne();
        $this->scheduler->add_task('lcs:synchronize_tickets gg-world')->onlyOne();
        $this->scheduler->add_task('lcs:update_tickets_prizes gg-world')->onlyOne();

        // GGWorldMillion sync
        $this->scheduler->add_task('lcs:update_draw_data gg-world-million')->onlyOne();
        $this->scheduler->add_task('lcs:synchronize_tickets gg-world-million')->onlyOne();
        $this->scheduler->add_task('lcs:update_tickets_prizes gg-world-million')->onlyOne();

        // GGWorldX sync
        $this->scheduler->add_task('lcs:update_draw_data gg-world-x')->onlyOne();
        $this->scheduler->add_task('lcs:synchronize_tickets gg-world-x')->onlyOne();
        $this->scheduler->add_task('lcs:update_tickets_prizes gg-world-x')->onlyOne();

        // GGWorldKeno sync
        $this->scheduler->add_task('lcs:update_draw_data gg-world-keno')->onlyOne();
        $this->scheduler->add_task('lcs:synchronize_tickets gg-world-keno')->onlyOne();
        $this->scheduler->add_task('lcs:update_tickets_prizes gg-world-keno')->onlyOne();
    }

    private function add_only_staging_tasks()
    {
        $this->scheduler->add_wget_empire_task('updatecurrencies')->daily();
        $this->scheduler->add_task('add_is_aff_user_flag_to_aff_users')->everyMinute(2)->onlyOne();

        // Global checks if whole system works correct
        // If not, these tasks send notification to PagerDuty or other AlertProvider
        $this->healthChecks();
    }

    private function add_only_production_tasks()
    {
        $this->scheduler->add_wget_empire('task/import_to_mautic')->everyMinute(MauticImport::getSchedulerIntervalInMinutes());
//        $this->scheduler->add_wget_empire('task/import_casino_data_to_mautic')->daily(2, 20);
        $this->scheduler->add_wget_empire_task('updatecurrencies')->hourly();
        $this->scheduler->add_task('log:checkErrorsPaymentLog')->daily(5, 20);
        $this->scheduler->add_task('log:checkErrorsRaffleLog')->daily(5, 30);

        #LCS
        // Double Jack
        $this->scheduler->add_task('lcs:update_draw_data double-jack')->onlyOne();
        $this->scheduler->add_task('lcs:synchronize_tickets double-jack')->onlyOne();
        $this->scheduler->add_task('lcs:update_tickets_prizes double-jack')->onlyOne();

        $this->scheduler->add_task('lcs:update_draw_data double-jack-million')->onlyOne();
        $this->scheduler->add_task('lcs:synchronize_tickets double-jack-million')->onlyOne();
        $this->scheduler->add_task('lcs:update_tickets_prizes double-jack-million')->onlyOne();

        $this->scheduler->add_task('lcs:update_draw_data double-jack-x')->onlyOne();
        $this->scheduler->add_task('lcs:synchronize_tickets double-jack-x')->onlyOne();
        $this->scheduler->add_task('lcs:update_tickets_prizes double-jack-x')->onlyOne();

        $this->scheduler->add_task('lcs:update_draw_data double-jack-keno')->onlyOne();
        $this->scheduler->add_task('lcs:synchronize_tickets double-jack-keno')->onlyOne();
        $this->scheduler->add_task('lcs:update_tickets_prizes double-jack-keno')->onlyOne();

        // Global checks if whole system works correct
        // If not, these tasks send notification to PagerDuty or other AlertProvider
        $this->healthChecks();

        // allocates commissions to casino affs from yesterday's purchases
        $this->scheduler->add_task('update_casino_commission_for_affs')->hourly();

        $this->scheduler->add_task('packages_audit')->daily(10);

        $this->scheduler->add_task('synchronize_primeads_aff')->daily(4, 30);

        // if all accounts are synchronized, this task can be commented out
        $this->scheduler->add_task('create_aff_for_all_users')->everyMinute(2)->onlyOne();

        $this->scheduler->add_task('add_is_aff_user_flag_to_aff_users')->everyMinute(2)->onlyOne();
    }

    private function addWpCron(): bool
    {
        try {
            /** @var Whitelabel[] */
            $whitelabels = Whitelabel::query()
                ->select('domain')
                ->get();
        } catch (\Throwable $e) {
            echo "failed to load whitelabels for wp cron " . $e->getMessage();
            return false;
        }

        $curlParams = "";
        if (Helpers_App::is_not_production_environment()) {
            $curlParams = " -k";
        }
        if (Helpers_App::is_staging_environment()) {
            $curlParams .= " -u whitelotto_test:Xv8VfcVv5Usr8QCbUrK3";
        }

        foreach ($whitelabels as $whitelabel) {
            $this->scheduler->add_command(["curl https://{$whitelabel->domain}/wp-cron-for-curl.php?doing_wp_cron $curlParams", []], true);
        }

        return true;
    }

    private function healthChecks(): void
    {
        $this->addHealthCheck(AbstractAlertListener::TYPE_PAGE_STATUS)->everyMinute(15);
        $this->addHealthCheck(AbstractAlertListener::TYPE_INCORRECT_NEXT_DRAW)->everyMinute(25);
        $this->addHealthCheck(AbstractAlertListener::TYPE_NEXT_DRAW_LISTENER)->at(Services_Scheduler::everyXHours(6));

        // Each health check below is run every 2 hours at specific time to not run simultaneously
        $this->addHealthCheck(AbstractAlertListener::TYPE_OUTDATED_LOTTERY)->at(Services_Scheduler::everyXHours(2, 15));
        $this->addHealthCheck(AbstractAlertListener::TYPE_UNPROCESSED_TICKETS)->at(Services_Scheduler::everyXHours(2, 20));
        $this->addHealthCheck(AbstractAlertListener::TYPE_UNPURCHASED_LCS_TICKETS)->at(Services_Scheduler::everyXHours(2, 25));
        //$this->addHealthCheck(AbstractAlertListener::TYPE_UNPURCHASED_TICKETS)->at(Services_Scheduler::everyXHours(2, 30));
        $this->addHealthCheck(AbstractAlertListener::TYPE_UNPAIDOUT_TICKETS)->at(Services_Scheduler::everyXHours(2, 35));
        //$this->addWordpressHealthCheck(AbstractAlertListener::TYPE_WORDPRESS_PAGES);
        $this->addHealthCheck(AbstractAlertListener::TYPE_MISSING_RAFFLE_DRAW)->at(Services_Scheduler::everyXHours(2, 40));
    }

    private function addHealthCheck(string $name): Job
    {
        return $this->scheduler->add_task('alert_listener ' . $name);
    }

    private function addWordpressHealthCheck(string $name): void
    {
        $logger = Container::get(FileLoggerService::class);
        try {
            $whitelabelRepository = Container::get(WhitelabelRepository::class);
            $domains = $whitelabelRepository->getAllActiveWhitelabelDomains();
            foreach ($domains as $domain) {
                $this->scheduler->add_wordpress_task($domain, "alert_listener $name")->daily(6);
            }
        } catch(Throwable $exception) {
            $logger->error("Something wrong while running wordpress health check: $name. Error: {$exception->getMessage()}");
        }
    }
}
