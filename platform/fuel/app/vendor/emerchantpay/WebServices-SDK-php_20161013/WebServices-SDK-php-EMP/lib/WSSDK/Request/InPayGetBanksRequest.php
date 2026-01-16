<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/InPayGetBanks.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

class InPayGetBanksRequest extends BaseRequest {

	public function __construct(Model\PaymentType\InPayGetBanks $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/inpay/getbanks", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

};

