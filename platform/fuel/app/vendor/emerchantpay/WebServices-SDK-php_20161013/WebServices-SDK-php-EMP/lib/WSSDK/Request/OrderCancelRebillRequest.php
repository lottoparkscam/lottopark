<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/OrderCancelRebill.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

/**
 * 
 */
class OrderCancelRebillRequest extends BaseRequest {

	public function __construct(Model\OrderCancelRebill $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/order/cancelrebill", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

	protected function buildResponse ($xmlString, $headers){
		return new StatusResponse($xmlString, $headers);
	}

};

