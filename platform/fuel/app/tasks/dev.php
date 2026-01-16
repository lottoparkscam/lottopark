<?php

namespace Fuel\Tasks;

use Fuel\Core\Cli;
use Fuel\Core\Fuel;
use Helpers_Time;
use Model_Lottery;
use Task_Cli;
use Task_Dev_Reset_User_Password;

/**
 * Development tasks.
 */
final class Dev extends Task_Cli
{
    public function __construct()
    {
        $this->disableOnProduction();
    }

    public function help(): void
    {
        echo "Commands:\r\n" .
            "1. php oil r dev:generate_tickets lottery_slug ?tickets_count=1000 ?lines_per_slip=7 ?lines_per_ticket=0 ?user_email=null ?multiplier=null ?numbers_per_line=null\r\n" .
            "   If user is not provided test@user.work|loc will be used. lines_per_ticket=0 will pick random number of lines\r\n" .
            "   E.g. php oil r dev:generate_tickets lotto-zambia 200 to create 200 tickets for lotto-zambia and default user.\r\n";
    }

    public function generate_tickets(
        string $lottery_slug,
        int $tickets_count = 1000,
        int $lines_per_slip = 7,
        int $lines_per_ticket = 0,
        string $user_email = null,
        ?int $multiplier = null,
        ?int $numbers_per_line = null
    ): void {
        switch (Fuel::$env) {
            case Fuel::STAGING:
                $user_email_default = 'test@user.work';
                break;
            case Fuel::DEVELOPMENT:
                $user_email_default = 'test@user.loc';
                break;
            default: // abort if not recognized environment
                exit("undefined environment");
        }
        $draw_date = Cli::option('draw_date');
        $user_email = $user_email ?: $user_email_default;

        $generation_task = \Task_Dev_Generate_Tickets::execute_task($user_email, $lottery_slug, $tickets_count, $lines_per_slip, $lines_per_ticket, $draw_date, $multiplier, $numbers_per_line);

        if ($generation_task->get_result()->is_successful()) {
            echo "Successfully generated $tickets_count tickets for user with email $user_email\r\n";
        } else {
            echo $generation_task->get_last_error_message();
        }
    }

    public function reset_user_password(int $user_id)
    {
        $task = Task_Dev_Reset_User_Password::execute_task($user_id);
        if ($task->get_result()->is_successful()) {
            echo "Successfully generated new password for user $user_id\r\n";
        } else {
            echo $task->get_last_error_message();
        }
    }

    public function generate_users(string $user_login_prefix, int $users_count = 1000, int $whitelabel_id = 1): void
    {
        $generation_task = \Task_Dev_Generate_Users::execute_task($user_login_prefix, $users_count, $whitelabel_id);

        if ($generation_task->get_result()->is_successful()) {
            echo "Successfully generated $users_count users";
        } else {
            echo $generation_task->get_last_error_message();
        }
    }

    public function generate_withdrawals(int $withdrawals_count = 10, int $user_id = 1, int $whitelabel_id = 1): void
    {
        $generation_task = \Task_Dev_Generate_Withdrawals::execute_task($withdrawals_count, $user_id, $whitelabel_id);

        if ($generation_task->get_result()->is_successful()) {
            echo "Successfully generated $withdrawals_count withdrawals";
        } else {
            echo $generation_task->get_last_error_message();
        }
    }

    public function sort_draw_dates()
    {
        $lotteries = Model_Lottery::get_all_enabled_lotteries();
        foreach ($lotteries['__by_id'] as $lottery) {
            try {
                if (!isset($lottery['draw_dates'])) {
                    continue;
                }
                $draw_dates_array = json_decode($lottery['draw_dates']);
                Helpers_Time::sort_weekdays_asc($draw_dates_array);
                if ($draw_dates_array === json_decode($lottery['draw_dates'])) {
                    \Helpers_Cli::info("Draw dates are already sorted");
                    continue;
                }
                $lottery = Model_Lottery::forge($lottery);
                $lottery->set([
                    'draw_dates' => json_encode($draw_dates_array)
                ]);
                $lottery->save();
            } catch (\Throwable $e) {
                \Helpers_Cli::error("Cannot sort draw dates for lottery {$lottery['slug']}. " . $e->getMessage());
            }
            \Helpers_Cli::success("Successfully sorted draw dates for lottery {$lottery['slug']}.");
        }
    }

    public function repair_ticket_draw_dates(): void
    {
        set_time_limit(600);
        $repairTask = RepairTicketDrawDates::execute_task();

        if ($repairTask->get_result()->is_successful()) {
            \Helpers_Cli::success("Successfully updated ticket draw dates");
        } else {
            \Helpers_Cli::error($repairTask->get_last_error_message());
        }
    }

    public function repair_multidraw_dates(): void
    {
        set_time_limit(600);
        $repairTask = RepairMultiDrawDates::execute_task();

        if ($repairTask->get_result()->is_successful()) {
            \Helpers_Cli::success("Successfully updated multidraw dates");
        } else {
            \Helpers_Cli::error($repairTask->get_last_error_message());
        }
    }

    public function repair_notifications_dates(): void
    {
        set_time_limit(600);
        $repairTask = RepairNotificationsDrawDates::execute_task();

        if ($repairTask->get_result()->is_successful()) {
            \Helpers_Cli::success("Successfully updated multidraw dates");
        } else {
            \Helpers_Cli::error($repairTask->get_last_error_message());
        }
    }

    public function repair_ticket_cost(): void
    {
        set_time_limit(600);
        $repairTask = RepairTicketCost::execute_task();

        if ($repairTask->get_result()->is_successful()) {
            \Helpers_Cli::success("Successfully updated ticket costs");
        } else {
            \Helpers_Cli::error($repairTask->get_last_error_message());
        }
    }
}
