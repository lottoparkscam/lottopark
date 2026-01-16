<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/OrderSettle.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;
use \WSSDK\BaseResponse as BaseResponse;

/**
 * 
 */
class OrderSettleRequest extends BaseRequest {

	public function __construct(Model\OrderSettle $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/order/settle", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

	protected function buildResponse ($xmlString, $headers){
		return new StatusResponse($xmlString, $headers);
	} 

};

