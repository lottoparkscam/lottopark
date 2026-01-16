<?php

use Fuel\Core\Config;
use Fuel\Core\Validation;
use Repositories\LottorisqLogRepository;
use Services\LtechService;

/**
 * Class for preparing Forms_Whitelabel_Edit form
 */
class Forms_Admin_Whitelabels_Ltech extends Forms_Main
{
    use Traits_Gets_States;

    /**
     *
     * @return Presenter_Admin_Whitelabels_Edit
     */
    public function get_inside(): Presenter_Admin_Whitelabels_Edit
    {
        return $this->inside;
    }

    public function check_lottorisqbalance($echo_only = false)
    {
        Config::load("lottorisq", true);
        $db_ltech_accounts = Model_Whitelabel_Ltech::find_by('is_enabled', 1);
        $ltech_accounts = [];
        if (!empty($db_ltech_accounts)) {
            foreach ($db_ltech_accounts as $ltech_account) {
                $ltech_accounts[] = $ltech_account->to_array();
            }
        }
        $default_ltech_account = [
            "id" => 0,
            "key" => Config::get("lottorisq.lottorisq.key"),
            "secret" => Config::get("lottorisq.lottorisq.secret"),
            "name" => "DEFAULT"
        ];
        $ltech_accounts = array_merge([$default_ltech_account], $ltech_accounts);
        foreach ($ltech_accounts as $ltech_account) {
            $this->process_lottorisqbalance($ltech_account, $echo_only);
        }
    }

    private function process_lottorisqbalance($ltech_account, $echo_only)
    {
        /** @var LottorisqLogRepository $lottorisqLogRepository */
        $lottorisqLogRepository = Container::get(LottorisqLogRepository::class);

        $log_whitelabel_id = isset($ltech_account['whitelabel_id']) ? $ltech_account['whitelabel_id'] : null;
        $log_ltech_account = $ltech_account['id'] != 0 ? $ltech_account['id'] : null;
        try {
            $ltech_helper = new Helpers_Ltech($ltech_account['id']);
            $ltech_request = $ltech_helper->sendLtechRequest('account');

            $response = $ltech_request['response'];
            $httpcode = $ltech_request['httpcode'];

            if ($response === false) {
                throw new Exception("CURL error while getting account balance from Lottorisq - curl_exec failed");
            }

            $data = json_decode($response);
            if (isset($data->error)) {
                throw new Exception("Trouble getting account balance from Lottorisq [" . $data->error->code . ": " . $data->error->message . "]");
            }
            if (empty($data->accounts) || !count($data->accounts)) {
                throw new Exception("Trouble getting account balance from Lottorisq (empty accounts).");
            }
            $accountData = [];
            $is_low = $echo_only ? true : false;
            foreach ($data->accounts as $account) {
                if (substr($account->name, 0, 7) == "Tickets") {
                    $accountData[] = [$account->currency, $account->balance];
                    if (round($account->balance, 2) <= 500 && round($account->balance, 2) != 0) {
                        $is_low = true;
                    }
                }
            }
            if ($is_low) {
                $accounts_list = "";
                if ($echo_only) {
                    $body = "L-TECH ACCOUNT: ".$ltech_account['name']." \n";
                } else {
                    $body = "One of the lottorisq account balances is low! L-TECH ACCOUNT: ".$ltech_account['name']." \n";
                }
                foreach ($accountData as $acc) {
                    $accounts_list .= "\nTickets (" . $acc[0] . "): " . $acc[1] . " " . $acc[0];
                }
                $body .= $accounts_list . "\n\n";

                $ltechService = Container::get(LtechService::class);
                $ticketsQueue = $ltechService->getSumsOfQueuedTicketPerCurrency();
                $priorityWhitelabelNames = [
                    'MegaJackpotPH',
                    'MegaJackpot',
                    'RedFoxLotto',
                    'LottoHoy',
                    'LottoMat',
                    'DoubleJack.Online',
                    'LottoPark'
                ];

                $body .= "SUMS OF QUEUED TICKETS: \n";

                $rowsPerCurrency = [];

                // Row means one line per whitelabel, currency and sum of costs weren't processed tickets
                foreach ($ticketsQueue as $row) {
                    $whitelabel = $row['whitelabel'];
                    $currencyCode = $row['currency_code'];
                    $cost = $row['cost'];
                    $whitelabelName = in_array($whitelabel, $priorityWhitelabelNames) ?
                        "<b>$whitelabel</b>" :
                        $whitelabel;

                    if (empty($rowsPerCurrency[$currencyCode])) {
                        $rowsPerCurrency[$currencyCode] = '';
                    }

                    $rowsPerCurrency[$currencyCode] .= "$whitelabelName ($currencyCode): $cost $currencyCode \n";
                }

                foreach ($rowsPerCurrency as $currencyCode => $line) {
                    $body .= "\n<b>$currencyCode</b>:\n$line";
                }

                if (!$echo_only) {
                    $lottorisqLogRepository->addWarningLog($log_whitelabel_id, null, null, "One of the L-Tech account is low in balance (" . $ltech_account['name'] . "). Sending notify e-mails.", $accountData, $log_ltech_account);
                    \Package::load('email');
                    Config::load("lotteries", true);
                    $recipients = Config::get("lotteries.ltech_low_balance_emails");
                    $email = Email::forge();
                    $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
                    $email->to($recipients);
                    $title = "Lotto Emergency: Low L-TECH [".$ltech_account['name']."] account balance notification";
                    $email->subject($title);
                    $email->body($body);
                    try {
                        $email->send();
                    } catch (Exception $e) {
                        $lottorisqLogRepository->addErrorLog($log_whitelabel_id, null, null, "[lottorisqbalance] " . $e->getMessage(), $log_ltech_account);
                    }

                    if (isset($ltech_account['whitelabel_id'])) {
                        $this->send_whitelabel_email($ltech_account['whitelabel_id'], $accounts_list, $log_ltech_account);
                    }
                } else {
                    echo str_replace("\n", "<br>", $body."\n\n");
                }
            }
        } catch (Exception $e) {
            $lottorisqLogRepository->addErrorLog($log_whitelabel_id, null, null, $e->getMessage(), $log_ltech_account);
        }
    }
    
    private function send_whitelabel_email($whitelabel_id, $accounts_list, $log_ltech_account)
    {
        /** @var LottorisqLogRepository $lottorisqLogRepository */
        $lottorisqLogRepository = Container::get(LottorisqLogRepository::class);

        $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id);

        \Package::load('email');
        $email = Email::forge();
        $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
        $email->to([
            $whitelabel['email'] => $whitelabel['name']]);
        $title = "Lotto Emergency: Low L-TECH account balance notification";
        $email->subject($title);
        $body = "Your whitelabel ".$whitelabel['name']." has low L-TECH account balance. Please top it up!\n";
        $body .= $accounts_list;

        $email->body($body);
        try {
            $email->send();
        } catch (Exception $e) {
            $lottorisqLogRepository->addErrorLog($whitelabel_id, null, null, "[lottorisqbalance] " . $e->getMessage(), $log_ltech_account);
        }
    }
}
