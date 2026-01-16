<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/RetrieveChargeback.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

class RetrieveChargebackRequest extends BaseRequest {

	public function __construct(Model\RetrieveChargeback $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/chargeback/search", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

};

