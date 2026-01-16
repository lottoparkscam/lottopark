<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/EarthportGetPayoutRequiredData.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

class EarthportGetPayoutRequiredDataRequest extends BaseRequest {

	public function __construct(Model\PaymentType\EarthportGetPayoutRequiredData $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/earthport/getpayoutrequiredfields", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

};

