<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

use \WSSDK\Model as Model;
use \WSSDK\Model\PaymentType as PaymentType;
use \WSSDK\BaseRequest as BaseRequest;

class CustomerRequest extends BaseRequest {

	public function __construct(Model\BaseModel $model, Model\Credentials $credentials, $APIDomain, $action, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/customer/$action", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

	static function Retrieve(Model\CustomerRetrieve $model, Model\Credentials $credentials, $APIDomain, $headers = null, $SSLVersion = 0, $test = false){
		return new self($model, $credentials, $APIDomain, 'search', $headers, $SSLVersion, $test);
	}

	static function Create(Model\CustomerCreate $model, Model\Credentials $credentials, $APIDomain, $headers = null, $SSLVersion = 0, $test = false){
		return new self($model, $credentials, $APIDomain, 'add', $headers, $SSLVersion, $test);
	}

	static function Update(Model\CustomerUpdate $model, Model\Credentials $credentials, $APIDomain, $headers = null, $SSLVersion = 0, $test = false){
		return new self($model, $credentials, $APIDomain, 'update', $headers, $SSLVersion, $test);
	}

	static function GetCards(Model\CustomerGetCards $model, Model\Credentials $credentials, $APIDomain, $headers = null, $SSLVersion = 0, $test = false){
		return new self($model, $credentials, $APIDomain, 'getcards', $headers, $SSLVersion, $test);
	}

};