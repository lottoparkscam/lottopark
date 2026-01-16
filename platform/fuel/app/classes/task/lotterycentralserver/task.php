<?php

use Carbon\Carbon;
use Fuel\Core\Config;
use Services\Logs\FileLoggerService;

/**
 * Archetype of tasks, that communicate with Lottery Central Server.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-07
 * Time: 11:36:30
 */
abstract class Task_Lotterycentralserver_Task extends Task_Task
{
    use Services_Api_Signature,
        Services_Api_Nonce;

    /**
     * Remember to call this constructor if you define constructor in child class.
     */
    protected function __construct()
    {
        parent::__construct();
        $this->load_config();
    }

    /**
     * Key of the sale point at Lottery Central Server.
     *
     * @var string
     */
    protected $key = null;
    /**
     * Secret of the sale point at Lottery Central Server.
     *
     * @var string
     */
    protected $secret = null;
    /**
     * Lottery Central Server base url.
     * @var string
     */
    private $base_url = null;

    /**
     * Load config for Lottery Central Server: key, secret, base_url.
     *
     * @return void
     */
    private function load_config(): void
    {
        Config::load('lottery_central_server', true);
        $this->key = Config::get('lottery_central_server.sale_point.key');
        $this->secret = Config::get('lottery_central_server.sale_point.secret');
        $this->base_url = Config::get('lottery_central_server.url.base');
    }

    /**
     * Build url for Lottery Central Server.
     *
     * @param string $relative_url relative_url without '/' prefix.
     * @return string absolute_url for provided relative url.
     */
    protected function absolute_url(string $relative_url): string
    {
        return $this->base_url . $relative_url;
    }

    /**
     * Create headers for request.
     * @param string $route route used to generate signature. Without '/' prefix
     * @param string $lottery_slug slug sent in headers.
     * @param array|null $data data used to generate signature. NOTE it will be json encoded. This will be omitted if param is missing or null.
     * @return array headers.
     */
    protected function headers(string $route, string $lottery_slug, ?array $data = null): array
    {
        $nonce = (string)$this->generate_nonce();
        $data = $data === null ? '' : json_encode($data); // encode data only if it is provided.
        $signature = $this->build_signature($this->secret, $nonce, '/' . $route, $data);

        return [
            Services_Curl::header('api-key', $this->key),
            Services_Curl::header('api-signature', $signature),
            Services_Curl::header('api-nonce', $nonce),
            Services_Curl::header('lottery-slug', $lottery_slug),
        ];
    }

    public static function shouldAddInsufficientBalanceLog(): bool
    {
        $hourBefore = Carbon::now()->subHour()->setMinute(59)->setSecond(0);
        $hourUntil = Carbon::now()->setMinute(1)->setSecond(0);
        $now = Carbon::now();

        return $now->between($hourBefore, $hourUntil);
    }

    /**
     * Evaluate response from LCS.
     * Minimally it will assert that response is not empty and doesn't contain error.
     * NOTE: I think that single expected key is sufficient 
     * - if response doesn't contain even one expected field, then we should exit with error.
     * @param mixed $response
     * @param string $expected_key optional key to check.
     * @return void
     * @throws \Exception if response is invalid.
     */
    protected function evaluate_response($response, string $expected_key = null): void
    {
        if (Services_Curl::isLtechInsufficientBalance()) {
            if (self::shouldAddInsufficientBalanceLog()) {
                $fileLoggerService = Container::get(FileLoggerService::class);
                $fileLoggerService->info('Received insufficient balance from Ltech');
            }

            exit("Received insufficient balance from Ltech \n");
        }

        if (!Services_Curl::is_last_request_status_success()) {
            throw new ErrorException('Request to LCS failed, cause: status code different than 200, response ' . var_export($response, true));
        }

        if (is_array($response) === false && empty($response)) { // first empty check - shouldn't happen or very rarely
            throw new \Exception('Request to LCS failed, cause: received empty response');
        }
        if (key_exists('error', $response)) { // check for error, if it's set then throw with it.
            throw new \Exception('Request to LCS failed, cause: ' . $response['error']);
        }
        // if there is no error check if expected key is set (naive but at this time sufficient) 
        if ($expected_key !== null && !key_exists($expected_key, $response)) {
            throw new \Exception("Request to LCS failed, cause: $expected_key not found in response, response " . var_export($response, true));
        }
    }
}
