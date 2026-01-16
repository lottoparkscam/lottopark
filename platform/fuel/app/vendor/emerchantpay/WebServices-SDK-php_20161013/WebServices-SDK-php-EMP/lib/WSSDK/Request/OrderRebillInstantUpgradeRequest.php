<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/OrderRebillInstantUpgrade.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

/**
 * 
 */
class OrderRebillInstantUpgradeRequest extends BaseRequest {

	public function __construct(Model\OrderRebillInstantUpgrade $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/order/instantupgrade", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

	protected function buildResponse ($xmlString, $headers){
		return new StatusResponse($xmlString, $headers);
	}

};

