<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;

class iDEALABNGetBanksRequest extends BaseRequest {

	public function __construct(Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/idealabn/getbanks", $headers, $SSLVersion, $test);
	}

};

