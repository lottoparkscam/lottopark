<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/InPayGetInstructions.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

class InPayGetInstructionsRequest extends BaseRequest {

	public function __construct(Model\PaymentType\InPayGetInstructions $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/inpay/getinstructions", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

};

