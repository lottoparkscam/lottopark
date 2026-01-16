<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class Moneta extends PaymentType {

	protected $paymentTypeKey = "monetas2s";

	protected $required = ['monetas2s_payer_id', 'monetas2s_payment_password'];

	public function __construct(){
		$this->fields = (object) [
			'monetas2s_payer_id' => null,
			'monetas2s_payment_password' => null,
			'payment_trans_type' => null
        ];
	}

	/* SETTER */
	public function setPayerId ($value){
		$this->fields->monetas2s_payer_id = $value;
	}
	public function setPayerPassword ($value){
		$this->fields->monetas2s_payment_password = $value;
	}
	public function setPaymentType ($value){
		$this->fields->payment_trans_type = $value;
	}

	/* GETTERS */
	public function getPayerId (){
		return $this->fields->monetas2s_payer_id;
	}
	public function getPayerPassword (){
		return $this->fields->monetas2s_payment_password;
	}
	public function getPaymentType (){
		return $this->fields->payment_trans_type;
	}

}