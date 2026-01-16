<?php

namespace WSSDK\Model;

use \WSSDK\Model as Model;

class ThreatMetrix extends RiskProvider {

	public function __construct($token){
		$this->fields = (object) [
			'thm_session_id' => $token,
        ];
	}

	/* GETTERS */
	public function getToken (){
		return $this->fields->thm_session_id;
	}

}