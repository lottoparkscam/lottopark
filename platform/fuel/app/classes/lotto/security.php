<?php

use GuzzleHttp\Client;
use Helpers\CaptchaHelper;
use Helpers\CountryHelper;
use Repositories\Aff\WhitelabelAffRepository;
use Repositories\IpLoginTryRepository;

/** @deprecated */
class Lotto_Security
{
    /**
     *
     * @return string
     */
    public static function generate_salt(): string
    {
        $salt = self::get_random_pseudobytes();
        return bin2hex($salt);
    }

    /**
     *
     * @param string $password
     * @param string $salt
     * @param string $type
     * @return string
     */
    public static function generate_hash(
        string $password,
        string $salt,
        string $type = "sha512"
    ): string {
        return hash($type, $salt . $password);
    }

    /**
     *
     * @param int $length
     * @return string|bool
     */
    public static function get_random_pseudobytes(int $length = 64)
    {
        $cstrong = false;
        $i = 0;
        $random = '';

        do {
            $random = openssl_random_pseudo_bytes($length, $cstrong);
            $i++;
            if ($i == 30) {
                //TODO: log error somewhere;
                break;
            }
        } while ($cstrong == false);

        return $random;
    }

    /**
     * @param int $whitelabel_id
     * @return string affiliate/subaffiliate token
     */
    public static function generate_aff_token(int $whitelabel_id): string
    {
        $randomTokenString = "";
        $whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);

        do {
            $randomTokenString = substr(self::generate_salt(), 0, 10);
            $affiliateCount = $whitelabelAffRepository
                ->findAffiliateCountByTokenOrSubToken($whitelabel_id, $randomTokenString);
        } while ((int)$affiliateCount > 0);

        return $randomTokenString;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return int
     */
    public static function generate_user_token(int $whitelabel_id): int
    {
        $random = 0;

        do {
            $random = random_int(100000000, 999999999);
            $users = DB::query("SELECT count(*) AS count FROM whitelabel_user WHERE whitelabel_id = :whitelabel AND token = :token");
            $users->param(":token", $random);
            $users->param(":whitelabel", $whitelabel_id);
            $users = $users->execute()->as_array();
        } while ($users[0]['count'] > 0);

        return $random;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return int
     */
    public static function generate_transaction_token(int $whitelabel_id): int
    {
        $random = 0;

        do {
            $random = random_int(100000000, 999999999);
            $transactions = DB::query("SELECT count(*) AS count FROM whitelabel_transaction WHERE whitelabel_id = :whitelabel AND token = :token");
            $transactions->param(":token", $random);
            $transactions->param(":whitelabel", $whitelabel_id);
            $transactions = $transactions->execute()->as_array();
        } while ($transactions[0]['count'] > 0);

        return $random;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return int
     */
    public static function generate_withdrawal_token(int $whitelabel_id): int
    {
        $random = 0;

        do {
            $random = random_int(100000000, 999999999);
            $withdrawals = DB::query("SELECT count(*) AS count FROM withdrawal_request WHERE whitelabel_id = :whitelabel AND token = :token");
            $withdrawals->param(":token", $random);
            $withdrawals->param(":whitelabel", $whitelabel_id);
            $withdrawals = $withdrawals->execute()->as_array();
        } while ($withdrawals[0]['count'] > 0);

        return $random;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return int
     */
    public static function generate_ticket_token(int $whitelabel_id): int
    {
        $random = 0;

        do {
            $random = random_int(100000000, 999999999);
            $tickets = DB::query("SELECT count(*) AS count FROM whitelabel_user_ticket WHERE whitelabel_id = :whitelabel AND token = :token");
            $tickets->param(":token", $random);
            $tickets->param(":whitelabel", $whitelabel_id);
            $tickets = $tickets->execute()->as_array();
        } while ($tickets[0]['count'] > 0);

        return $random;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return int
     */
    public static function generate_multidraw_token(int $whitelabel_id): int
    {
        $random = 0;

        do {
            $random = random_int(100000000, 999999999);
            $tickets = DB::query("SELECT count(*) AS count FROM multi_draw WHERE whitelabel_id = :whitelabel AND token = :token");
            $tickets->param(":token", $random);
            $tickets->param(":whitelabel", $whitelabel_id);
            $tickets = $tickets->execute()->as_array();
        } while ($tickets[0]['count'] > 0);

        return $random;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return int
     */
    public static function generate_whitelabel_campaign_token(int $whitelabel_id): int
    {
        $random = 0;

        do {
            $random = random_int(100000000, 999999999);
            $campaigns = DB::query("SELECT count(*) AS count FROM whitelabel_campaign WHERE whitelabel_id = :whitelabel AND token = :token");
            $campaigns->param(":token", $random);
            $campaigns->param(":whitelabel", $whitelabel_id);
            $campaigns = $campaigns->execute()->as_array();
        } while ($campaigns[0]['count'] > 0);

        return $random;
    }

    /**
     *
     * @param int $length
     * @return string
     */
    public static function generate_promo_code_token(int $length): string
    {
        $token = '';
        do {
            for ($i = 0; $i < $length; $i++) {
                $token .= self::generate_alphanum_byte();
            }

            $codes = DB::query("SELECT count(*) AS count FROM whitelabel_promo_code WHERE token = :token");
            $codes->param(":token", $token);
            $codes = $codes->execute()->as_array();
        } while ($codes[0]['count'] > 0);

        return $token;
    }

    /**
     *
     * @return string
     */
    public static function generate_alphanum_byte(): string
    {
        $byte = '';

        do {
            $ps_byte = openssl_random_pseudo_bytes(1);

            if ($ps_byte === false) {
                continue;
            }

            if (
                ($ps_byte[0] >= '0' &&  $ps_byte[0] <= '9') ||
                ($ps_byte[0] >= 'A' &&  $ps_byte[0] <= 'Z')
            ) {
                $byte = $ps_byte;
            }
        } while ($byte === '');
        return $byte;
    }

    /**
     *
     * @param string $salt
     * @param \DateTime $time_valid
     * @return string
     */
    public static function generate_time_hash(string $salt, \DateTime $time_valid): string
    {
        $random = self::get_random_pseudobytes();

        $hash_time_formatted = $time_valid->format('Y-m-d H:i:s');

        $password = $random .
            $hash_time_formatted .
            Config::get("security.activation_key");

        $hash_time = Lotto_Security::generate_hash($password, $salt, "sha256");

        return $hash_time;
    }

    /**
     *
     * @param string $login Unused
     * @param string $password
     * @return bool
     */
    public static function check_whitelabel_credentials($login, $password)
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $hash = self::generate_hash($password, $whitelabel['salt']);

        if ($hash !== $whitelabel['hash']) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param string $login
     * @param string $hash
     * @return bool
     */
    public static function check_whitelabel_credentials_hashed($login, $hash)
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        if ($hash !== $whitelabel['hash']) {
            return false;
        }
        return true;
    }

    public static function reset_IP(): bool
    {
        $ip = self::get_IP();
        $ipLoginTryRepository = Container::get(IpLoginTryRepository::class);

        $ipLoginTry = $ipLoginTryRepository->findByIp($ip);
        if (!$ipLoginTry) {
            return false;
        }

        $updatedIpLoginTry = $ipLoginTryRepository->updateById(
            $ipLoginTry->id,
            0
        );

        if(!$updatedIpLoginTry) {
            return false;
        }

        return true;
    }

    /**
     *
     * @return string
     */
    public static function get_IP(): string
    {
        $ip = "";
        $got_ip = false;

        // That block is only for dev and derived environments
        if (Helpers_General::is_test_env()) {
            Config::load("platform", true);
            $ip = Config::get("platform.iptest.ip");
            if (!empty($ip)) {
                $got_ip = true;
            }
        }

        if (!$got_ip) {
            // prepare for cloudflare
            if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }

        return $ip;
    }

    /**
     *
     * @return bool
     */
    public static function check_captcha(): bool
    {
        $userIP = Lotto_Security::get_IP();
        $useHcaptcha = CountryHelper::isIPFromCountries($userIP, CaptchaHelper::HCAPTCHA_COUNTRIES);

        if ($useHcaptcha) {
            return self::check_hcaptcha();
        }

        return self::check_recaptcha();
    }

    /**
     *
     * @return bool
     */
    public static function check_recaptcha(): bool
    {
        if (Helpers_General::is_development_env()) {
            return true;
        }
        Config::load('recaptcha', true);

        $data = [
            'secret' => Config::get('recaptcha.keys.secret_key'),
            'response' => Input::post('g-recaptcha-response'),
            'remoteip' => self::get_IP(),
        ];

        $client = new Client();
        $response = $client->request(
            'POST',
            CaptchaHelper::RECAPTCHA_VERIFICATION_URL,
            [
                'form_params' => $data,
                'timeout' => Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS
            ]
        );

        $responseData = json_decode($response->getBody(), true);

        return is_array($responseData) && $responseData['success'];
    }

    /**
     *
     * @return bool
     */
    public static function check_hcaptcha(): bool
    {
        if (Helpers_General::is_development_env()) {
            return true;
        }

        Config::load("hcaptcha", true);

        $data = [
            'secret' => Config::get("hcaptcha.keys.secret_key"),
            'response' => Input::post('h-captcha-response'),
        ];

        $client = new Client();
        $response = $client->request(
            'POST',
            CaptchaHelper::HCAPTCHA_VERIFICATION_URL,
            [
                'form_params' => $data,
                'timeout' => Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS
            ]
        );

        $responseData = json_decode($response->getBody(), true);
        $success = is_array($responseData) && $responseData['success'];

        if ($success) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param string $ip
     * @param string $network
     * @param int $cidr
     * @return bool
     */
    public static function check_IP_range($ip, $network, $cidr)
    {
        if ((ip2long($ip) & ~((1 << (32 - $cidr)) - 1)) == ip2long($network)) {
            return true;
        }

        return false;
    }

    public static function check_IP(): bool
    {
        $ip = self::get_IP();
        $ipLoginTryRepository = Container::get(IpLoginTryRepository::class);

        $ipLoginTry = $ipLoginTryRepository->findByIp($ip);
        if (!$ipLoginTry) {
            $lastLoginTry = (new DateTime('now', new DateTimeZone('UTC')))->format(Helpers_Time::DATETIME_FORMAT);
            $ipLoginTry = $ipLoginTryRepository->insert([
                'ip' => $ip,
                'last_login_try_at' => $lastLoginTry,
                'login_try_count' => 0
            ]);
        }
        else {
            $lastLoginTry = DateTime::createFromFormat(
                Helpers_Time::DATETIME_FORMAT, 
                $ipLoginTry->last_login_try_at, 
                new DateTimeZone('UTC')
            );
        }

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $sub = clone $now;
        $sub->sub(new DateInterval('PT30M'));

        if ($lastLoginTry < $sub) { 
            $ipLoginTry = $ipLoginTryRepository->updateById(
                $ipLoginTry->id,
                0
            );
        }

        if (isset($ipLoginTry) && $ipLoginTry->login_try_count <= 5) {
            $ipLoginTryRepository->updateFloatField(
                $ipLoginTry->id,
                'login_try_count',
                1,
                ['last_login_try_at' => $now->format(Helpers_Time::DATETIME_FORMAT)]
            );
        }

        $ipLoginTry = $ipLoginTryRepository->findByIp($ip);
        if ($ipLoginTry->login_try_count <= 5) {
            return true;
        }

        return false;
    }
}
