<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/RetrieveOrder.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

/**
 * @example examples/submitOrder.php using the WSSDK class
 */
class RetrieveOrderRequest extends BaseRequest {

	public function __construct(Model\RetrieveOrder $Order, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/order/search", $headers, $SSLVersion, $test);
		$this->body['Order'] = $Order;
	}

};

