<?php
namespace WSSDK;
require_once __DIR__."/../Xml.php";
require_once __DIR__."/../Response/BaseResponse.php";

/**
 * Exeption thrown by errors in the building and validation a response
 */
class RequestValidationException extends \Exception { }

/**
 * A simple object used to mange the inclusion of the 'test_transaction' property
 * if the model is included the request will be handles as a test request.
 */
class Test extends Model\BaseModel {

	public function __construct(){
		$this->fields = (object) ['test_transaction'	=> 1];
	}

}

/**
 * The baseRequest is the back bone class used for creating and managing request to a hosted WS API.
 * It is responsable for marshaling, validating, and serializing the required models a request needs.
 */
abstract class BaseRequest {

	/**
	 * The final WS API endpoint that will recieve the POST request
	 * @var String
	 */
	private $endpoint;
	/**
	 * Headers to be sent with the request
	 * @var Object
	 */
	private $headers;
	/**
	 * The dynamic container of WSSDK\Model s to process with the request. These are managed internaly and added via exposed public methods
	 * @var array<WSSDK\Model>
	 */
	protected $body = [];

	/**
	 * The minimum required headers
	 */
	static $REQUIRED_HEADERS = ['User-Agent','Accept'];

	/**
	 * Builds the base request instance
	 * @param Model\Credentials $credentials Credentials model containing client_id and api_key
	 * @param String            $endpoint    The final WS API endpoint that will recieve the POST request
	 * @param Orray            $headers     Headers to be sent with the request
	 * @param boolean           $isTest      Weather the request is a test, and should include the test_transaction=1 value.
	 */
	public function __construct(Model\Credentials $credentials, $endpoint, $headers = null, $SSLVersion = 0, $isTest = false){
		$this->endpoint = $endpoint;
		$this->headers 	= is_null($headers) ? (object) [] : $headers;
		$this->body['credentials'] = $credentials;

		if ($isTest){
			$this->body['test'] = new Test();
		}

	}

	/**
	 * run internaly by the send method to validate the request before sending
	 * can be extended with custom logic for specific request classes
	 * @throws RequestValidationException on failed validation
	 * @return void
	 */
	protected function validate(){

		foreach (self::$REQUIRED_HEADERS as $key){
			if (!property_exists($this->headers, $key) || is_null($this->headers->{$key})){
				throw new RequestValidationException(get_class($this) . ": Request header \"$key\" is required to be set", 0);
			}
		}

	}

	/**
	 * Adds a specific header to the request
	 * @param String
	 * @param String
	 */
	public function setHeader($key, $value){
		$this->headers->{$key} = $value;
	}
	/**
	 * Adds an array of headers to the request
	 * @param Object map of key value hear pairs
	 */
	public function setHeaders($headers){
		foreach($headers as $key => $value) {
		    $this->headers->{$key} = $value;
		}
	}

	/**
	 * called internaly by the send method to construct a response instance
	 * can be extended to return sub types of BaseResponse
	 * @param  String body from request
	 * @param  Object headers object
	 * @return BaseResponse
	 */
	protected function buildResponse ($xmlString, $headers){
		return new BaseResponse($xmlString, $headers);
	}

	/**
	 * builds, validates and sends a request
	 * @param  Boolean DEBUG Weather to var_dump the request and response data
	 * @throws ModelValidationException
	 * @throws RequestValidationException
	 * @return BaseResponse
	 */
	public function send($DEBUG = false) {

		if (!is_string($this->endpoint)){
			throw new RequestValidationException(get_class($this) . ': Endpoint strings is required');
		}

		$context = $this->body;

		// build body objects into url form urlencoded string
		// validate at the model level
		$bodyArray = [];
		foreach ($this->body as $key => $model){
			array_push($bodyArray, $model->Serialize());
		}
		$body = implode("&", $bodyArray);

		if ($DEBUG){
			var_dump("Request: $this->endpoint");
			var_dump("URL_FORM_ENCODED_STRING:");
			var_dump($body);
		}

		// will throw exeptions if request is not valid;
		// validate at the request level
		$this->validate();

		// build request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSLVERSION, 0); //CURL_SSLVERSION_TLSv1_2
		curl_setopt($ch, CURLOPT_URL, $this->endpoint);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Expect:"]);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec ($ch);

		if ($DEBUG){
			var_dump("SERVER_RESPONSE:");
			var_dump($server_output);
		}

		// get headers
		list($headers, $body) = explode("\r\n\r\n", $server_output, 2);
		$headers = self::parseHeaders($headers);

		curl_close ($ch);

		return $this->buildResponse($body, $headers);

	}

	/**
	 * Parse headers from curl into Object
	 * @param  http header string
	 * @return Object map of headers
	 */
	private function parseHeaders($header_text){
		$headers = [];
	    foreach (explode("\r\n", $header_text) as $i => $line)
	        if ($i === 0)
	            $headers['http_code'] = $line;
	        else
	        {
	            list ($key, $value) = explode(': ', $line);

	            $headers[$key] = $value;
	        }

	    return $headers;
	}
}

