<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class InPay extends PaymentType {

	protected $paymentTypeKey = "inpay";

	protected $required = ['inpay_bank_id', 'inpay_order_text'];

	public function __construct(){
		$this->fields = (object) [
			'inpay_bank_id' => null,
			'inpay_order_text' => null
        ];
	}

	/* SETTER */
	public function setBankId ($value){
		$this->fields->inpay_bank_id = $value;
	}
	public function setOrderText ($value){
		$this->fields->inpay_order_text = $value;
	}

	/* GETTERS */
	public function getBankId (){
		return $this->fields->inpay_bank_id;
	}
	public function getOrderText (){
		return $this->fields->inpay_order_text;
	}

}