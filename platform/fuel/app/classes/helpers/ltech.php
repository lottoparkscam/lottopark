<?php

use Repositories\LottorisqLogRepository;

/**
 * Ltech helper
 */
class Helpers_Ltech
{
    /**
     * @var $whitelabel
     */
    private $whitelabel_ltech_id;

    /**
     * @var string
     */
    private $config_name = 'lottorisq';

    /**
     * @var string
     */
    private $config_group = 'lottorisq';

    /**
     * @var string
     */
    private $config_ext = '';

    public const INSUFFICIENT_BALANCE_HTTP_CODE = 402;

    /**
     * Helpers_Ltech constructor.
     * @param $whitelabel
     */
    public function __construct($whitelabel_ltech_id = null)
    {
        $this->whitelabel_ltech_id = $whitelabel_ltech_id;
    }

    private static array $ltechLastResponseCodePerAccount;

    private static function isInsufficientBalanceSetForCurrentInstance(?int $whitelabel_ltech_id, int $lotteryCurrencyID): bool
    {
        self::$ltechLastResponseCodePerAccount[$whitelabel_ltech_id][$lotteryCurrencyID] = self::$ltechLastResponseCodePerAccount[$whitelabel_ltech_id][$lotteryCurrencyID] ?? null;

        return self::$ltechLastResponseCodePerAccount[$whitelabel_ltech_id][$lotteryCurrencyID] == 402;
    }

    public function sendLtechRequest(string $endpointPath, $request = null, $whitelabelLtechId = null, int $lotteryCurrencyID = 0): array
    {
        if (self::isInsufficientBalanceSetForCurrentInstance($whitelabelLtechId, $lotteryCurrencyID)) {
            return [
                'response' => 'skipped due to insufficient balance',
                'httpcode' => self::INSUFFICIENT_BALANCE_HTTP_CODE,
            ];
        }

        // Get ltech details
        $ltech_details = $this->get_ltech_details($whitelabelLtechId);

        // CURL REQUEST
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $ltech_details['endpoint'] . $endpointPath);
        curl_setopt($ch, CURLOPT_USERPWD, $ltech_details['key']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (!empty($request)) {
            // Encode request to json
            $json_request = json_encode($request);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_request);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type:application/json",
            "Cache-Control:no-cache"
        ]);

        $ssl_verifypeer = 2;
        $ssl_verifyhost = 2;

        if (Helpers_General::is_development_env()) {
            $ssl_verifypeer = 0;
            $ssl_verifyhost = 0;
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // store code
        self::$ltechLastResponseCodePerAccount[$whitelabelLtechId][$lotteryCurrencyID] = $httpcode;

        curl_close($ch);

        // Return response and http code
        return [
            'response' => $response,
            'httpcode' => $httpcode
        ];
    }

    /**
     * @return array
     */
    public function get_ltech_details($whitelabel_ltech_id = null)
    {
        $whitelabel_ltech_details = null;

        if ($whitelabel_ltech_id !== null) {
            $whitelabel_ltech_details = Model_Whitelabel_Ltech::find_by_pk($whitelabel_ltech_id);
        } elseif ($whitelabel_ltech_id === null && $this->whitelabel_ltech_id !== null) {
            $whitelabel_ltech_details = Model_Whitelabel_Ltech::find_by_pk($this->whitelabel_ltech_id);
        }

        $ltech_config_details = $this->get_config_details();

        // If whitelabel have his own key - get and replace it
        if (!empty($whitelabel_ltech_details)) {
            $ltech_config_details['ltech_id'] = $whitelabel_ltech_details['id'];
            $ltech_config_details['key'] = $whitelabel_ltech_details['key'];
            $ltech_config_details['secret'] = $whitelabel_ltech_details['secret'];
            $ltech_config_details['whitelabel_ltech_record'] = $whitelabel_ltech_details;
        } else {
            $ltech_config_details['whitelabel_ltech_record']['id'] = null;
            $ltech_config_details['whitelabel_ltech_record']['locked'] = 0;
            $ltech_config_details['whitelabel_ltech_record']['can_be_locked'] = 0;
            $ltech_config_details['whitelabel_ltech_record']['name'] = 'DEFAULT';
        }

        return $ltech_config_details;
    }

    /**
     * @return array
     */
    private function get_config_details()
    {
        // Load config
        Config::load($this->config_name . $this->config_ext, true);

        // Get config variables
        $pre_config_var = $this->config_name . '.' . $this->config_group . '.';

        $endpoint = Config::get($pre_config_var . "endpoint");
        $key = Config::get($pre_config_var . "key");
        $confirm = Config::get($pre_config_var . "confirm");
        $prefix = Config::get($pre_config_var . "prefix");
        $secret = Config::get($pre_config_var . "secret");
        $multiplier = Config::get($pre_config_var . "multiplier");

        return [
            'ltech_id' => null,
            'endpoint' => $endpoint,
            'key' => $key,
            'confirm' => $confirm,
            'prefix' => $prefix,
            'secret' => $secret,
            'multiplier' => $multiplier
        ];
    }

    /**
     * @param $ltech_details
     * @param $lottery
     * @return bool
     */
    public function lock_ltech_sales($ltech_details, $lottery)
    {
        if (empty($ltech_details['whitelabel_ltech_record'])) {
            return false;
        }

        $ltech_record = Model_Whitelabel_Lottery::find_by_pk($lottery['wid']);
        if ($ltech_details['whitelabel_ltech_record']['can_be_locked'] == "1" && $ltech_record->ltech_lock == 0) {
            $ltech_record->ltech_lock = 1;
            $ltech_record->save();

            // Lock other lotteries with same currency code
            $this->lock_lotteries_by_currency($ltech_record['whitelabel_id'], $lottery['currency_id']);

            // Send email to Whitelabel
            $this->send_whitelabel_email($ltech_record['whitelabel_id'], $lottery['currency'], $ltech_record['id']);

            // Send email to Admins
            $this->send_admins_email($ltech_record['whitelabel_id'], $lottery['currency'], $ltech_record['id']);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function check_lock_ltech()
    {
        // Get ltech details
        $ltech_details = $this->get_ltech_details();

        if (empty($ltech_details['whitelabel_ltech_record'])) {
            return false;
        }

        if ($ltech_details['whitelabel_ltech_record']['locked'] == "1") {
            return true;
        }

        return false;
    }

    private function send_whitelabel_email($whitelabel_id, $currency_code, $log_ltech_account)
    {
        /** @var LottorisqLogRepository $lottorisqLogRepository */
        $lottorisqLogRepository = Container::get(LottorisqLogRepository::class);

        $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id);

        \Package::load('email');
        $email = Email::forge();
        $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
        $email->to([
            $whitelabel['email'] => $whitelabel['name']
        ]);
        $title = "Lotto Emergency: Lotteries with " . $currency_code . " currency have been locked";
        $email->subject($title);
        $body = "The purchase of " . $currency_code . " lotteries on your " . $whitelabel['name'] . " white-label has been blocked due to insufficient balance on your L-Tech account. Please top up and contact support to unlock.";

        $email->body($body);
        try {
            $email->send();
        } catch (Exception $e) {
            $lottorisqLogRepository->addErrorLog($whitelabel['id'], null, null, "[lottorisqbalance] " . $e->getMessage(), $log_ltech_account);
        }
    }


    private function send_admins_email($whitelabel_id, $currency_code, $log_ltech_account)
    {
        /** @var LottorisqLogRepository $lottorisqLogRepository */
        $lottorisqLogRepository = Container::get(LottorisqLogRepository::class);

        $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id);

        Config::load("lotteries", true);
        $recipients = Config::get("lotteries.support_errors_emails");
        \Package::load('email');
        $email = Email::forge();
        $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
        $email->to($recipients);
        $title = "Lotto Emergency: Lotteries with " . $currency_code . " currency have been locked on " . $whitelabel['name'];
        $email->subject($title);
        //$body = "Your whitelabel ".$whitelabel['name']." have locked lotteries with currency ".$currency_code.", cause of not topped up account balance on this currency. Please top up and contact support!\n";
        $body = "The purchase of " . $currency_code . " lotteries on " . $whitelabel['name'] . " white-label has been blocked due to insufficient balance on their L-Tech account.";

        $email->body($body);
        try {
            $email->send();
        } catch (Exception $e) {
            $lottorisqLogRepository->addErrorLog($whitelabel['id'], null, null, "[lottorisqbalance] " . $e->getMessage(), $log_ltech_account);
        }
    }

    private function lock_lotteries_by_currency($whitelabel_id, $currency_id)
    {
        $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id);

        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);

        foreach ($lotteries['__by_id'] as $id => $lottery) {
            if ($lottery['currency_id'] == $currency_id && $lottery['provider'] == 1) {
                $ltech_record = Model_Whitelabel_Lottery::find_by_pk($lottery['wid']);

                $ltech_record->ltech_lock = 1;
                $ltech_record->save();
            }
        }

        Lotto_Helper::clear_cache(["model_lottery", "model_whitelabel"]);
    }
}
