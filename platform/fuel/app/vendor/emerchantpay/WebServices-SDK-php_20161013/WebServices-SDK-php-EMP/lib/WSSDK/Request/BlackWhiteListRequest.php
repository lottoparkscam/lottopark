<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/ListSearch.php";

use \WSSDK\Model as Model;
use \WSSDK\Model\PaymentType as PaymentType;
use \WSSDK\BaseRequest as BaseRequest;

abstract class ListRequest extends BaseRequest {

	public function __construct(Model\ListSearch $model, Model\Credentials $credentials, $APIDomain, $list, $action, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/$list/$action" , $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

	protected function buildResponse ($xmlString, $headers){
		return new StatusResponse($xmlString, $headers);
	} 

};


class AddToBlackListRequest extends ListRequest {
	public function __construct(Model\BlackListSearch $model, Model\Credentials $credentials, $APIDomain, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($model, $credentials, $APIDomain, 'blacklist', 'add', $headers, $SSLVersion, $test);
	}
}
class RemoveFromBlacklistRequest extends ListRequest {
	public function __construct(Model\BlackListSearch $model, Model\Credentials $credentials, $APIDomain, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($model, $credentials, $APIDomain, 'blacklist', 'remove', $headers, $SSLVersion, $test);
	}
}
class AddToWhitelistRequest extends ListRequest {
	public function __construct(Model\WhiteListSearch $model, Model\Credentials $credentials, $APIDomain, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($model, $credentials, $APIDomain, 'blacklist', 'add', $headers, $SSLVersion, $test);
	}
}
class RemoveFromWhitelistRequest extends ListRequest {
	public function __construct(Model\WhiteListSearch $model, Model\Credentials $credentials, $APIDomain, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($model, $credentials, $APIDomain, 'blacklist', 'add', $headers, $SSLVersion, $test);
	}
}

