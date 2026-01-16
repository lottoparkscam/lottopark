<?php

namespace WSSDK\Model;

use \WSSDK\Model as Model;

class Infocapture extends RiskProvider {

	public function __construct($token){
		$this->fields = (object) [
			'infocapture_token' => $token,
        ];
	}

	/* GETTERS */
	public function getToken (){
		return $this->fields->infocapture_token;
	}

}