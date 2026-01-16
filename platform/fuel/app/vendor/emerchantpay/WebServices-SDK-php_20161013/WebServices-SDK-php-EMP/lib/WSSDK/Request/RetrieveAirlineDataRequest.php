<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/RetrieveAirlineData.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

/**
 * 
 */
class RetrieveAirlineDataRequest extends BaseRequest {

	public function __construct(Model\RetrieveAirlineData $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/airline/search", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

};

