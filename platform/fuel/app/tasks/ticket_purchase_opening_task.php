<?php

namespace Fuel\Tasks;

use Container;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use Models\Raffle;
use Models\WhitelabelRaffleTicketLine;
use Services\Logs\FileLoggerService;
use Throwable;
use Wrappers\Cli;

/**
 * Class resets temporary disabled raffles (should be places in cron, every minutes).
 *
 * @UnitTest not testable due impossible to mock system date (Carbon can help)
 */
final class Ticket_Purchase_Opening_Task
{
    private Raffle $raffle_dao;
    private WhitelabelRaffleTicketLine $ticketLineDao;
    private Cli $cli;
    private FileLoggerService $fileLoggerService;

    public function __construct()
    {
        $this->raffle_dao = Container::get(Raffle::class);
        $this->ticketLineDao = Container::get(WhitelabelRaffleTicketLine::class);
        $this->cli = Container::get(Cli::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    public function help(): void
    {
        $this->cli->write([
            'Commands:',
            '      php oil r ticket_purchase_opening_task',
        ]);
    }

    public function run(): void
    {
        $raffles = $this->get_temporary_disabled_raffles();

        if (empty($raffles)) {
            $this->cli->write('There are no raffles to reset.');
            return;
        }

        foreach ($raffles as $raffle) {
            try {
                $isFullySynchronized = $this->ticketLineDao->get_all_unsynchronized_lines_count($raffle->id) === 0;

                if (!$isFullySynchronized) {
                    $this->cli->write(sprintf('Raffle is temporary disabled, and attempted to enable it, but some lines has not been synchronized yet so skipped.'));
                }

                $time_zone = new DateTimeZone($raffle->timezone);
                $time_in_raffle_time_zone = new DateTime('now', $time_zone);
                $open_dates = $raffle->sell_open_dates_objects;

                # if there no open dates defined, we assumes it should be reset
                if (empty($open_dates)) {
                    throw new InvalidArgumentException('Sell_open_dates must be defined when sell limitation is enabled.');
                }

                $next_same_week_day = array_filter($open_dates, function (DateTime $date) use ($time_in_raffle_time_zone) {
                    return $date->format('D') === $time_in_raffle_time_zone->format('D');
                });

                if (empty($next_same_week_day)) {
                    continue;
                }

                $next_open_sell_date = reset($open_dates);

                if ($time_in_raffle_time_zone->getTimestamp() >= $next_open_sell_date->getTimestamp()) {
                    $this->reset($raffle);
                }
            } catch (Throwable $e) {
                $this->handle_exception($e);
            }
        }
    }

    /**
     * @return array|Raffle[]
     */
    private function get_temporary_disabled_raffles(): array
    {
        return $this->raffle_dao->get_temporary_disabled();
    }

    private function reset(Raffle $raffle): void
    {
        $this->cli->write(sprintf('Raffle %s sell date restarted', $raffle->slug));
        $raffle->is_sell_enabled = true;
        $this->raffle_dao->store($raffle);
    }

    private function handle_exception(Throwable $e): void
    {
        $this->fileLoggerService->error(
            sprintf($e->getMessage())
        );
    }
}
