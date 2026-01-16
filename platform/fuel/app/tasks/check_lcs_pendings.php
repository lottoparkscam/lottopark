<?php

namespace Fuel\Tasks;

use Carbon\Carbon;
use DateInterval;
use DateTime;
use DateTimeZone;
use Fuel\Core\Config;
use Fuel\Core\DB;
use Helpers_Time;
use Services\Logs\FileLoggerService;
use Container;

class Check_LCS_Pendings
{
    private const CURRENT_DRAW = 0;
    private const PREVIOUS_DRAWS = 1;

    public function run(): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $pending_tickets = DB::query($this->get_query(self::CURRENT_DRAW))->execute()->as_array();
        $current_date = new DateTime("now", new DateTimeZone("UTC"));
        $trigger_date = (clone $current_date)->sub(new DateInterval("PT10M"));

        $email_pending_current = [];
        $email_pending_previous = DB::query($this->get_query(self::PREVIOUS_DRAWS))->execute()->as_array();

        if (count($pending_tickets)) {
            foreach ($pending_tickets as $pending_ticket) {
                $transaction_confirmation_date = empty($pending_ticket['date_confirmed']) ? null : new DateTime($pending_ticket['date_confirmed'], new DateTimeZone("UTC"));
                $ticket_draw_date = new DateTime($pending_ticket['draw_date'], new DateTimeZone($pending_ticket['timezone']));
                $before_draw_date = (clone $ticket_draw_date)->sub(new DateInterval("PT24H"));
                // Check if ticket hasn't been bought for at least 10 minutes and if there is 24 hours before the draw
                if (($transaction_confirmation_date == null) || ($transaction_confirmation_date < $trigger_date)) {
                    if ($current_date > $before_draw_date) {
                        $email_pending_current[] = $pending_ticket;
                    }
                }
            }
        }

        if (count($email_pending_current) || count($email_pending_previous)) {
            $body = "";
            if (count($email_pending_current)) {
                $body .= "Tickets pending for the current draw (total: " . count($email_pending_current) . "):\n";
                foreach ($email_pending_current as $key => $ticket) {
                    $body .= "[" . $key . "] Ticket ID: " . $ticket['ticket_id'] . ", Token: " . $ticket['token'] . ", WhiteLabel: " . $ticket['name'] . " [" . $ticket['whitelabel_id'] . "], Lottery: " . $ticket['lottery_name'] . ", Purchase date [UTC]: " . $ticket['date'] . ", Draw date [Lottery]: " . $ticket['draw_date'] . "\n";
                }
            }
            if (count($email_pending_previous)) {
                if (count($email_pending_current)) {
                    $body .= "\n\n";
                }
                $body .= "Tickets pending for the previous draws (total: " . count($email_pending_previous) . "):\n";
                foreach ($email_pending_previous as $key => $ticket) {
                    $body .= "[" . $key . "] Ticket ID: " . $ticket['ticket_id'] . ", Token: " . $ticket['token'] . ", WhiteLabel: " . $ticket['name'] . " [" . $ticket['whitelabel_id'] . "], Lottery: " . $ticket['lottery_name'] . ", Purchase date [UTC]: " . $ticket['date'] . ", Draw date [Lottery]: " . $ticket['draw_date'] . "\n";
                }
            }
            if (!empty($body)) {
                Config::load("lotteries", true);
                $recipients = Config::get("lotteries.emergency_emails");
                \Package::load('email');
                $email = \Email::forge();
                $email->from('noreply@' . \Helpers_General::get_domain(), 'Lotto Emergency');
                $email->to($recipients);
                $title = "Lotto Emergency: Unsynchronised LCS tickets";
                $email->subject($title);
                $email->body($body);
                try {
                    $email->send();
                } catch (Exception $e) {
                    $error_message = "There is a problem with delivering the mail. " .
                        "Description of error: " . $e->getMessage();
                    $fileLoggerService->error(
                        $error_message
                    );
                }
                echo $body;
            }
        }
    }

    private function get_query($type = self::CURRENT_DRAW): string
    {
        $sign = "<";
        if ($type === self::CURRENT_DRAW) {
            $sign = "=";
        }

        $fourHoursAgo = Carbon::now('UTC')->subHours(4)->format(Helpers_Time::DATETIME_FORMAT);

        $sql = "SELECT w.name, wut.date, wt.date_confirmed, wut.whitelabel_id, wut.draw_date, l.name AS lottery_name, l.timezone, wut.id AS ticket_id,
        wut.token
        FROM whitelabel_user_ticket wut
        LEFT JOIN whitelabel_transaction wt ON wt.id = wut.whitelabel_transaction_id
        LEFT JOIN whitelabel_lottery wl ON wl.lottery_id = wut.lottery_id
        LEFT JOIN whitelabel w ON w.id = wut.whitelabel_id
        LEFT JOIN lottery_provider lp ON lp.id = wl.lottery_provider_id
        LEFT JOIN lottery l ON wut.lottery_id = l.id
        WHERE wl.whitelabel_id = wut.whitelabel_id
        AND wut.paid = 1
        AND wut.status = 0
        AND lp.provider = 3
        AND wut.is_synchronized = 0
        AND wut.date <= '" . $fourHoursAgo . "'
        AND wut.draw_date " . $sign . " l.next_date_local";

        return $sql;
    }
}
