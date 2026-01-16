<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/RetrieveVBV3D.php";

use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest;
use \WSSDK\BaseResponse as BaseResponse;

/**
 * 
 */
class RetrieveVBV3DRequest extends BaseRequest {

	public function __construct(Model\RetrieveVBV3D $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/vbvmc3d/result", $headers, $SSLVersion, $test);
		$this->body['model'] = $model;
	}

};

