<?php

namespace WSSDK\Model;

use \WSSDK\Model as Model;

class IovationRM extends RiskProvider {

	public function __construct($data){
		$this->fields = (object) [
			'ioblackbox' => $data,
        ];
	}

	/* GETTERS */
	public function getToken (){
		return $this->fields->ioblackbox;
	}

}