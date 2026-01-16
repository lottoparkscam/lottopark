<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/MerchantManagedRebill.php";

use \WSSDK\Model as Model;
use \WSSDK\Model\PaymentType as PaymentType;
use \WSSDK\BaseRequest as BaseRequest;

class MerchantManagedRebillRequest extends BaseRequest {

	public function __construct(Model\MerchantManagedRebill $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/order/rebill", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

	public function setPaymentType(PaymentType\PaymentType $model){
		// TODO: is this restricted to just creditcard and PayPal ??
		$this->body['PaymentType'] = $model;
	}

};

