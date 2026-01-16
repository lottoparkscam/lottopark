<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/RetrieveTransaction.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

class RetrieveTransactionRequest extends BaseRequest {

	public function __construct(Model\RetrieveTransaction $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/transaction/search", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

};

