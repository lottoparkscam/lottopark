<?php

/**
 * Curl concretization for our application.
 * @deprecated use Guzzle instead.
 */
final class Services_Curl
{
    /**
     * Base options for the curl.
     */
    const BASE_OPTIONS =
    [
        CURLOPT_RETURNTRANSFER => true, //return response, instead of outputting
        CURLOPT_TIMEOUT => 25,
    ];

    const JSON_CONTENT = 'application/json';
    const XML_CONTENT = 'application/xml';

    /**
     * Code for success status.
     */
    const SUCCESS = 200;

    public const LTECH_INSUFFICIENT_BALANCE_CODE = 402;

    /**
     * Last request result code.
     * @var int
     */
    private static $last_request_result_code = -1;

    /**
     * Get last request result code.
     *
     * @return  int
     */
    public static function get_last_request_result_code(): int
    {
        return self::$last_request_result_code;
    }

    /**
     * Check if last request status code was indicating success
     *
     * @return boolean
     */
    public static function is_last_request_status_success(): bool
    {
        return self::$last_request_result_code === self::SUCCESS;
    }

    public static function isLtechInsufficientBalance(): bool
    {
        return self::$last_request_result_code === self::LTECH_INSUFFICIENT_BALANCE_CODE;
    }

    /**
     * Make request via curl.
     *
     * @param string $url
     * @param array $curl_options
     * @return string result of the request.
     * @throws \Exception if request failed.
     */
    private static function request(string $url, array $curl_options): string
    {
        //create a new cURL resource
        $curl = curl_init($url);

        if (\Fuel::$env === \Fuel::DEVELOPMENT) {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = 0;
            $curl_options[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        // set curl options NOTE: base ones will be overwritten by provided ones (if there are duplicates).
        $options = $curl_options + self::BASE_OPTIONS;
        curl_setopt_array($curl, $options);

        //execute request
        $result = curl_exec($curl);

        // store result code
        self::$last_request_result_code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $curl_error = curl_error($curl);

        //close cURL resource
        curl_close($curl);

        // validate result
        if ($result === false) {
            throw new \Exception("Curl execution returned false. Error = $curl_error");
        }

        return $result;
    }

    /**
     * Wrapper for request, which will merge headers and union them to curl_options.
     *
     * @param string $url
     * @param array $base_headers
     * @param array $additional_headers
     * @param array $curl_options optional options.
     * @return string
     */
    private static function request_merge_headers(string $url, array $base_headers, array $additional_headers, array $curl_options = []): string
    {
        $merged_options =
            [
                CURLOPT_HTTPHEADER => array_merge($base_headers, $additional_headers),
            ]
            +
            $curl_options;

        return self::request($url, $merged_options);
    }

    /**
     * Make POST request via curl.
     * NOTE: headers will not overwrite, so make sure you don't pass Content-Type:
     * Also it's not hard to allow flexibility here but I don't think it's necessary.
     *
     * @param string $url
     * @param array $parameters
     * @param array $headers additional headers, empty on default.
     * NOTE: each header entry should contain only value in format: key: value
     * @return string result of the request.
     * @throws \Exception if request failed.
     */
    public static function post(string $url, array $parameters, array $headers = []): string
    {
        return self::request_merge_headers(
            $url,
            [ // base headers
                self::header('Content-Type', 'application/x-www-form-urlencoded')
            ],
            $headers,
            [
                CURLOPT_POST => count($parameters),
                CURLOPT_POSTFIELDS => http_build_query($parameters),
            ]
        );
    }

    /**
     * Make POST request via curl.
     * NOTE: you need to provide headers like Content-Type
     *
     * @param string $url
     * @param array $parameters
     * @param array $headers additional headers, empty on default.
     * NOTE: each header entry should contain only value in format: key: value
     * @return string result of the request.
     * @throws \Exception if request failed.
     */
    public static function postWithHeaders(string $url, array $parameters, array $headers = []): string
    {
        return self::request_merge_headers(
            $url,
            [],
            $headers,
            [
                CURLOPT_POST => count($parameters),
                CURLOPT_POSTFIELDS => http_build_query($parameters),
            ]
        );
    }

    /**
     * Make POST JSON request via curl.
     * NOTE: headers will not overwrite, so make sure you don't pass Content-Type:
     * Also it's not hard to allow flexibility here but I don't think it's necessary.
     *
     * @param string $url
     * @param array $parameters
     * @param array $headers additional headers, empty on default.
     * NOTE: each header entry should contain only value in format: key: value
     * @return string result of the request.
     * @throws \Exception if request failed.
     */
    public static function post_json(string $url, array $parameters, array $headers = []): string
    {
        return self::json($url, $parameters, $headers);
    }

    /**
     * Make GET JSON request via curl with payload outside of http query.
     * NOTE: headers will not overwrite, so make sure you don't pass Content-Type:
     * Also it's not hard to allow flexibility here but I don't think it's necessary.
     *
     * @param string $url
     * @param array $parameters
     * @param array $headers additional headers, empty on default.
     * NOTE: each header entry should contain only value in format: key: value
     * @return string result of the request.
     * @throws \Exception if request failed.
     */
    public static function get_json_with_payload(string $url, array $parameters, array $headers = []): string
    {
        return self::json($url, $parameters, $headers, 'GET');
    }

    /**
     * Make JSON request via curl.
     * NOTE: headers will not overwrite, so make sure you don't pass Content-Type:
     * Also it's not hard to allow flexibility here but I don't think it's necessary.
     *
     * @param string $url
     * @param array $parameters
     * @param array $headers additional headers, empty on default.
     * @param string $method method of the request, either GET or POST.
     * NOTE: each header entry should contain only value in format: key: value
     * @return string result of the request.
     * @throws \Exception if request failed.
     */
    private static function json(string $url, array $parameters, array $headers = [], string $method = 'POST'): string
    {
        $content = json_encode($parameters);

        return self::request_merge_headers(
            $url,
            [ // base headers
                self::header('Content-Type', self::JSON_CONTENT),
                self::header('Content-Length', strlen($content)), // NOTE: auto-cast to string
            ],
            $headers,
            [
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => $content,
            ]
        );
    }

    /**
     * Make GET request via curl.
     *
     * @param string $url
     * @param array $headers additional headers, empty on default.
     * NOTE: each header entry should contain only value in format: key: value
     * @return string result of the request.
     */
    public static function get_json(string $url, array $headers = []): string
    {
        return self::get($url, self::JSON_CONTENT, $headers);
    }

    public static function getJsonAsBrowser(string $url, array $headers = []): string
    {
        return self::request_merge_headers(
            $url,
            [
                self::header('Content-Type', self::JSON_CONTENT)
            ],
            $headers,
            [
                CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)'
            ]
        );
    }

    public static function getXmlAsBrowser(string $url, array $headers = []): string
    {
        return self::request_merge_headers(
            $url,
            [
                self::header('Content-Type', self::XML_CONTENT)
            ],
            $headers,
            [
                CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)'
            ]
        );
    }

    public static function postJsonAsBrowser(string $url, array $parameters, array $headers = []): string
    {
        $content = json_encode($parameters);

        return self::request_merge_headers(
            $url,
            [ // base headers
                self::header('Content-Type', self::JSON_CONTENT),
                self::header('Content-Length', strlen($content)), // NOTE: auto-cast to string
            ],
            $headers,
            [
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $content,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
            ]
        );
    }
    /**
     * Make GET request via curl.
     *
     * @param string $url
     * @param string $content_type you should use consts from this class.
     * @param array $headers additional headers, empty on default.
     * NOTE: each header entry should contain only value in format: key: value
     * @return string result of the request.
     * @throws \Exception if request failed.
     */
    public static function get(string $url, string $content_type, array $headers = []): string
    {
        return self::request_merge_headers(
            $url,
            [ // base headers
                self::header('Content-Type', $content_type)
            ],
            $headers
        );
    }

    public static function getHTMLAsBrowser(string $url, array $headers = []): string
    {
        return self::request_merge_headers(
            $url,
            [
                self::header('Content-Type', 'text/html; charset=utf-8')
            ],
            $headers,
            [
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36'
            ]
        );
    }


    /**
     * Create header entry.
     *
     * @param string $key
     * @param string $value
     * @return string
     */
    public static function header(string $key, string $value): string
    {
        return "$key: $value";
    }
}
