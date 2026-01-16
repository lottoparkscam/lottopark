<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class PayPal extends PaymentType {

	protected $paymentTypeKey = "paypal";

	protected $required = ['payment_trans_type'];

	public function __construct(){
		$this->fields = (object) [
			'payment_trans_type' => null
        ];
	}

	/* SETTER */
	public function setTransactionType ($value){
		$this->fields->payment_trans_type = $value;
	}

	/* GETTERS */
	public function getTransactionType (){
		return $this->fields->payment_trans_type;
	}

}