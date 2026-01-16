<?php

namespace WSSDK\Model;
// require_once __DIR__."/Model.php";

use \WSSDK\Model as Model;

/**
 * Contains merchant credentials used to access the WS API
 */
class Credentials extends Model\BaseModel {

	protected $required = ['client_id', 'api_key'];

	public function __construct($client_id = null, $api_key = null){

		$this->fields = (object) [
			'client_id' => $client_id,
			'api_key' => $api_key
        ];

	}

	public function setClientId ($value){
		$this->fields->client_id = $value;
	}
	public function setApiKey ($value){
		$this->fields->api_key = $value;
	}
	public function getClientId (){
		return $this->fields->client_id;
	}
	public function getApiKey (){
		return $this->fields->api_key;
	}

}



