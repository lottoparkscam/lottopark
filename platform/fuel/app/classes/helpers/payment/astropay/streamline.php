<?php

/**
 * Example of using the class:
 * require_once 'AstroPayStreamline.class.php';
 * $aps = new AstroPayStreamline();
 * $banks = $apd->get_banks_by_country('BR', 'json');
 *
 * Available functions:
 * - newinvoice($invoice, $amount, $bank, $country, $iduser, $cpf, $name, $email, $currency, $description, $bdate, $address, $zip, $city, $state, $return_url, $confirmation_url)
 * - get_status($invoice)
 * - get_exchange($country = 'BR', $amount = 1) {befault: $country = BR | $amount = 1}
 * - get_banks_by_country($country, $type) {befault: $country = BR | $type = 'json'}
 *
 */

use Helpers\UrlHelper;

/**
 * Class of AstroPay Streamline
 *
 * @author Santiago del Puerto (santiago@astropay.com)
 * @version 1.0
 *
 */
final class Helpers_Payment_Astropay_Streamline
{
    const PRODUCTION_URL = 'https://api.directa24.com/';
    const TESTING_URL = 'https://api-stg.directa24.com/';

    /**
     * Result code for the success.
     */
    const SUCCESS = 0;

    /**************************
     * Merchant configuration *
     **************************/
    private $x_login = '***';
    private $x_trans_key = '***';

    private $secret_key = '***';

    /*********************************
     * End of Merchant configuration *
     *********************************/

    /*****************************************************
     * ---- PLEASE DON'T CHANGE ANYTHING BELOW HERE ---- *
     *****************************************************/

    /**
     * Urls used in astro pay API.
     *
     * @var array
     */
    private $urls = [
        'new_invoice' => '',
        'status' => '',
        'exchange' => '',
        'banks' => ''
    ];

    /**
     * Get urls used in astro pay API.
     * Keys:
     * 'new_invoice'
     * 'status'
     * 'exchange'
     * 'banks'
     *
     * @return  array
     */
    public function get_urls()
    {
        return $this->urls;
    }

    /**
     * Count of errors.
     *
     * @var integer
     */
    private $errors = 0;

    /**
     * Create astro pay streamline object.`
     *
     * @param string $x_login Your merchant ID in Astropay platform
     * @param string $x_trans_key Your merchant password in Astropay platform
     * @param string $secret_key key for the control parameter.
     * @param string $sandbox true if in sandbox (test) environment.
     */
    public function __construct(string $x_login, string $x_trans_key, string $secret_key, bool $sandbox = true)
    {
        $this->urls['new_invoice'] = self::PRODUCTION_URL . 'api_curl/streamline/NewInvoice';
        $this->urls['status'] = self::PRODUCTION_URL . 'apd/webpaystatus';
        $this->urls['exchange'] = self::PRODUCTION_URL . 'apd/webcurrencyexchange';
        $this->urls['banks'] = self::PRODUCTION_URL . 'api_curl/apd/get_banks_by_country';

        if ($sandbox) {
            $this->urls['new_invoice'] = self::TESTING_URL . 'api_curl/streamline/NewInvoice';
            $this->urls['status'] = self::TESTING_URL . 'apd/webpaystatus';
            $this->urls['exchange'] = self::TESTING_URL . 'apd/webcurrencyexchange';
            $this->urls['banks'] = self::TESTING_URL . 'api_curl/apd/get_banks_by_country';
        }

        $this->x_login = $x_login;
        $this->x_trans_key = $x_trans_key;
        $this->secret_key = $secret_key;
    }

    /**
     * Create new invoice
     *
     * @param string $invoice Unique transaction identification at the merchant site.
     * @param string $amount Transaction amount (in the currency entered in the field “x_currency”)
     * @param string $bank Payment method code. See payment method codes
     * To check the payment methods available for your account, you can use function: get banks by country
     * @param string $country User’s country. in ISO 3166-1 alpha-2 codes
     * @param string $iduser Unique user id at the merchant side
     * @param string $cpf User’s personal identification number: BR: CPF/CNPJ, AR: DNI, UY: CI, MX: CURP/RFC/IFE, PE: DNI, CL: RUT, CO: CC, TK: ID
     * @param string $name User’s full name.
     * @param string $email User’s email address.
     * @param array $optionals array for optional parameters, empty on default.
     * Possible optionals (remember you need to provide valid keys):
     *  x_currency Transaction currency in ISO 4217. Mandatory if amount is present
     *  x_description Transaction description
     *  x_bdate User’s date of birth (Format: YYYYMMDD)
     *  x_address User’s address. Mandatory if country is BR (Brazil)
     *  x_zip User’s zip/postal code. Mandatory if country is BR (Brazil)
     *  x_city User’s city
     *  x_state User’s state. Brazilian 2 letter format
     *  x_return_url To be provided if the return URL is different from the return URL registered by the merchant. See return url
     *  x_confirmation_url To be provided if the confirmation URL is different from the confirmation URL registered by the merchant. See confirmation url
     * @return string|bool curl result. bool false on failure.
     */
    public function new_invoice(string $invoice, string $amount, string $bank, string $country, string $iduser, string $cpf, string $name, string $email, array $optionals = [])
    {
        $parameters = [
            //Mandatory
            'x_login' => $this->x_login,
            'x_trans_key' => $this->x_trans_key,
            'x_invoice' => $invoice,
            'x_amount' => $amount,
            'x_bank' => $bank,
            'type' => 'json',
            'x_country' => $country,
            'x_iduser' => $iduser,
            'x_cpf' => $cpf,
            'x_name' => $name,
            'x_email' => $email,
        ];

        // attach optionals
        $parameters = array_merge($parameters, $optionals);
        $zip = $parameters['x_zip'] ?? '';
        $bdate = $parameters['x_bdate'] ?? '';
        $address = $parameters['x_address'] ?? '';
        $city = $parameters['x_city'] ?? '';
        $state = $parameters['x_state'] ?? '';

        $message = $invoice . 'V' . $amount . 'I' . $iduser . '2' . $bank . '1' . $cpf . 'H' . $bdate . 'G' . $email . 'Y' . $zip . 'A' . $address . 'P' . $city . 'S' . $state . 'P';
        $control = strtoupper(hash_hmac('sha256', pack('A*', $message), pack('A*', $this->secret_key)));
        $parameters['control'] = $control;

        UrlHelper::urlencode_array($parameters);
        $response = $this->curl($this->urls['new_invoice'], $parameters);
        return $response;
    }

    /**
     * Get banks by country.
     *
     * @param string $country
     * @param string $type type of result, json on default.
     * @return string|bool curl result. bool false on failure.
     */
    public function get_banks_by_country(string $country = 'BR', string  $type = 'json')
    {
        $parameters = [
            //Mandatory
            'x_login' => $this->x_login,
            'x_trans_key' => $this->x_trans_key,
            'country_code' => $country,
            'type' => $type
        ];

        $response = $this->curl($this->urls['banks'], $parameters);
        return $response;
    }

    /**
     * Last sent parameters in raw form
     *
     * @var array
     */
    private $parameters = null;
    /**
     * Last sent parameters in http query form
     *
     * @var string
     */
    private $parameters_http_query = null;


    /**
     * END OF PUBLIC INTERFACE
     */
    /**
     * Make request via curl.
     *
     * @param string $url
     * @param array $parameters
     * @return string|bool curl result. bool false on failure.
     */
    private function curl(string $url, array $parameters)
    {
        $parameters_http_query = [];
        foreach ($parameters as $key => $value) {
            $parameters_http_query[] = "{$key}={$value}";
        }
        $parameters_http_query = join('&', $parameters_http_query);

        // store parameters
        $this->parameters = $parameters;
        $this->parameters_http_query = $parameters_http_query;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters_http_query);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        if (($error = curl_error($ch)) != false) {
            $this->errors++;

            if ($this->errors >= 5) {
                return $response;
            }

            sleep(1);
            $this->curl($url, $parameters);
        }
        curl_close($ch);

        $this->errors = 0;
        return $response;
    }

    /**
     * Get last sent parameters in raw form
     *
     * @return  array
     */
    public function get_parameters()
    {
        return $this->parameters;
    }

    /**
     * Get last sent parameters in http query form
     *
     * @return  string
     */
    public function get_parameters_http_query()
    {
        return $this->parameters_http_query;
    }
}
