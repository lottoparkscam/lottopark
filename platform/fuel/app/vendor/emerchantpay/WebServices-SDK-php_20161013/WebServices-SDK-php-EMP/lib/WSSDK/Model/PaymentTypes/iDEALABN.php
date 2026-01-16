<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class IdealAbn extends PaymentType {

	protected $paymentTypeKey = "idealabn";

	protected $required = ['idealabn_bank_id'];

	public function __construct(){
		$this->fields = (object) [
			'idealabn_bank_id' => null
        ];
	}

	/* SETTER */
	public function setBankId ($value){
		$this->fields->idealabn_bank_id = $value;
	}

	/* GETTERS */
	public function getBankId (){
		return $this->fields->idealabn_bank_id;
	}

}