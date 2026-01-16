<?php
/**
  * @filesource
  * known as CFT Without Previouse Order
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/OrderDirectCFT.php";
require_once __DIR__."/../Model/OrderDirectCFTCustomer.php";
require_once __DIR__."/../Model/OrderDirectCFTPaymentType.php";
require_once __DIR__."/../Model/OrderDirectCFTCreditCard.php";

use \WSSDK\Model as Model;
use \WSSDK\Model\PaymentType as PaymentType;
use \WSSDK\BaseRequest as BaseRequest;

/**
 * known as CFT Without Previouse Order
 */
class OrderDirectCFTRequest extends BaseRequest {

	public function __construct(Model\OrderDirectCFT $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/payout/submit", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

	protected function buildResponse ($xmlString, $headers){
		return new StatusResponse($xmlString, $headers);
	} 

	public function setCustomer(Model\OrderDirectCFTCustomer $model){
		$this->body['Customer'] = $model;
	}

	public function setPaymentType(PaymentType\OrderDirectCFTPaymentType $model){
		$this->body['PaymentType'] = $model;
	}

};

