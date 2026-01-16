<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class Neteller extends PaymentType {

	protected $paymentTypeKey = "neteller";

	protected $required = ['neteller_net_account', 'neteller_secure_id'];

	public function __construct(){
		$this->fields = (object) [
			'neteller_net_account' => null,
			'neteller_secure_id' => null
        ];
	}

	/* SETTER */
	public function setNetAccount ($value){
		$this->fields->neteller_net_account = $value;
	}
	public function setSecureId ($value){
		$this->fields->neteller_secure_id = $value;
	}

	/* GETTERS */
	public function getNetAccount (){
		return $this->fields->neteller_net_account;
	}
	public function getSecureId(){
		return $this->fields->neteller_secure_id;
	}

}