<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/RetrieveFraudData.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

class RetrieveFraudDataRequest extends BaseRequest {

	public function __construct(Model\RetrieveFraudData $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/risk/frauddata", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

};

