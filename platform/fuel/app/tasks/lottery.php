<?php

namespace Fuel\Tasks;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Fuel\Core\Cli;
use Fuel\Core\Config;
use Lotto_Helper;
use Model_Lottery;
use Model_Setting;
use Repositories\LotteryRepository;
use Services\Logs\FileLoggerService;
use function libxml_use_internal_errors;
use function set_time_limit;
use Container;

/**
 * Lottery task.
 */
final class Lottery
{
    private FileLoggerService $fileLoggerService;
    private LotteryRepository $lotteryRepository;

    public function __construct()
    {
        $this->lotteryRepository = Container::get(LotteryRepository::class);
    }

    public function run(): void
    {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    private function evaluate_result(string $slug, int $updated_count, string $action = "disabled"): void
    {
        switch ($updated_count) {
            case 1:
                echo "Successfully $action $slug\r\n";
                \Lotto_Helper::clear_cache(['model_lottery', 'model_whitelabel']);
                echo "Successfully cleared cache for model_lottery and model_whitelabel\r\n";

                return;
            default:
                echo "Something went wrong for $slug, updated_count=$updated_count. It might be already $action.\r\n";

                return;
        }
    }

    /**
     * Disable lottery
     * NOTE: it will clear cache on success.
     */
    public function disable(string $slug): void
    {
        $isDisabledLottery = $this->lotteryRepository->setLotteryUnplayableBySlug($slug);
        $this->evaluate_result($slug, (int)$isDisabledLottery);
    }

    /**
     * Disable lottery
     * NOTE: it will clear cache on success.
     */
    public function enable(string $slug): void
    {
        $isEnabledLottery = $this->lotteryRepository->setLotteryPlayableBySlug($slug);
        $this->evaluate_result($slug, (int)$isEnabledLottery, 'enabled');
    }

    /**
     * Update lottery results
     * NOTE: it will clear cache on success.
     */
    public function update_draw_data(string $slug = null): void
    {
        set_time_limit(0);
        libxml_use_internal_errors(true);

        $lotteries = Model_Lottery::getLotteries($slug ?? null);

        if ($lotteries === null) {
            Cli::error('Lotteries not found');

            return;
        }

        $lottery_classes = Model_Lottery::LOTTERY_CLASSES;

        foreach ($lotteries['__by_id'] as $lottery) {
            if (!isset($lottery_classes[$lottery['slug']])) {
                continue;
            }
            if ($lottery['is_enabled'] && $lottery['is_temporarily_disabled'] == 0) {
                try {
                    /** @var \Lotto_Lotteries_Feed $lotteryUpdateClassObject */
                    $lotteryUpdateClassObject = new $lottery_classes[$lottery['slug']];
                    $lotteryUpdateClassObject->set_lottery($lottery);
                    $lotteryUpdateClassObject->get_results();
                    Cli::write("{$lottery['name']} draw check finished. Check lottery_log.");
                } catch (Exception $e) {
                    Cli::error("{$lottery['name']} Update error, check logs.");
                    Model_Lottery::lottery_error($e, $lottery);
                    $this->lotteryUpdateError();
                }
            }
        }
        Model_Setting::update_setting('admin_firsterror', '');
    }

    /**
     * Update keno lottery results
     * NOTE: it will clear cache on success.
     */
    public function update_keno_draw_data(string $slug = null): void 
    {
        set_time_limit(0);
        libxml_use_internal_errors(true);

        $lotteries = Model_Lottery::getLotteries($slug ?? null);

        if ($lotteries === null) {
            Cli::error('Lotteries not found');
            return;
        }

        $lottery_classes = Model_Lottery::LOTTERY_CLASSES_KENO;

        foreach ($lotteries['__by_id'] as $lottery) {
            if (!isset($lottery_classes[$lottery['slug']])) {
                continue;
            }
            if ($lottery['is_enabled'] && $lottery['is_temporarily_disabled'] == 0) {
                try {
                    /** @var \Lotto_Lotteries_Feed $lotteryUpdateClassObject */
                    $lotteryUpdateClassObject = new $lottery_classes[$lottery['slug']];
                    $lotteryUpdateClassObject->set_lottery($lottery);
                    $lotteryUpdateClassObject->get_results();
                    Cli::write("{$lottery['name']} draw check finished. Check lottery_log.");
                } catch (Exception $e) {
                    Cli::error("{$lottery['name']} Update error, check logs.");
                    Model_Lottery::lottery_error($e, $lottery);
                    $this->lotteryUpdateError();
                }
            }
        }
        Model_Setting::update_setting('admin_firsterror', '');
    }

    public function lotteryUpdateError()
    {
        // TODO: refactor or move to another place
        $settings = Model_Setting::get_settings('admin');
        $now = new DateTime("now", new DateTimeZone("UTC"));
        $admin_firsterror = clone $now;
        if (!empty($settings['admin_firsterror'])) {
            $admin_firsterror = DateTime::createFromFormat('Y-m-d H:i:s', $settings['admin_firsterror'], new DateTimeZone("UTC"));
        } else {
            Model_Setting::update_setting('admin_firsterror', $now->format("Y-m-d H:i:s"));
        }
        $trigger = clone $now;
        $trigger->sub(new DateInterval('PT5H'));
        if ($trigger > $admin_firsterror) {
            Model_Setting::update_setting('admin_firsterror', '');
            \Package::load('email');
            Config::load("lotteries", true);
            $recipients = Config::get("lotteries.emergency_emails");
            $email = Email::forge();
            $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
            $email->to($recipients);
            $title = "Lotto Emergency: Lottery Update Critical Error!";
            $email->subject($title);
            $email->body("Some of the lotteries haven't been updated for longer than 5 hours! Please check the logs and decide what to do (change lottery source, disable the lottery, contact admin)!");
            try {
                $email->send();
            } catch (Exception $e) {
                $error_message = "There is a problem with delivering the mail. " .
                    "Description of error: " . $e->getMessage();
                $this->fileLoggerService->error(
                    $error_message
                );
            }
        }
    }
}
