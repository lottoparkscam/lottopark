<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class MBGateway extends PaymentType {

	protected $paymentTypeKey = "mbgateway";

	protected $required = ['account_name', 'iban', 'bic'];

	public function __construct(){
		$this->fields = (object) [
			'account_name' => null,
			'iban' => null,
			'bic' => null
        ];
	}

	/* SETTER */
	public function setAccountName($value){
		$this->fields->account_name = $value;
	}
	public function setIban($value){
		$this->fields->iban = $value;
	}
	public function setBic($value){
		$this->fields->bic = $value;
	}

	/* GETTERS */
	public function getAccountName(){
		return $this->fields->account_name;
	}
	public function getIban(){
		return $this->fields->iban;
	}
	public function getBic(){
		return $this->fields->bic;
	}

}