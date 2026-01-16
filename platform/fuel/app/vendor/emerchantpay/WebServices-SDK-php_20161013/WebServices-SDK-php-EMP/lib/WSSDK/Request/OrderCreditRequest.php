<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/OrderCredit.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

/**
 * 
 */
class OrderCreditRequest extends BaseRequest {

	public function __construct(Model\OrderCredit $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/order/credit", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

	protected function buildResponse ($xmlString, $headers){
		return new StatusResponse($xmlString, $headers);
	} 

};

