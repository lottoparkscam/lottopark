<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class OrderDirectCFTCreditCard extends OrderDirectCFTPaymentType {

	protected $paymentTypeKey = "creditcard";

	protected $required = [
		'card_number',
		'exp_month',
		'exp_year'
		//'credit_card_trans_type'
    ];

	public function __construct(){

		$this->fields = (object) [
			'card_holder_name' => null,
			'card_number' => null,
			'exp_month' => null,
			'exp_year' => null,
			'payment_type' => $this->paymentTypeKey
        ];
	}

	/* SETTER */
	public function setName ($value){
		$this->fields->card_holder_name = $value;
	}
	public function setNumber ($value){
		$this->fields->card_number = $value;
	}
	public function setExpiryMonth ($value){
		$this->fields->exp_month = $value;
	}
	public function setExpiryYear ($value){
		$this->fields->exp_year = $value;
	}

	/* GETTERS */
	public function getName (){
		return $this->fields->card_holder_name;
	}
	public function getNumber (){
		return $this->fields->card_number;
	}
	public function getExpiryMonth (){
		return $this->fields->exp_month;
	}
	public function getExpiryYear (){
		return $this->fields->exp_year;
	}

}

