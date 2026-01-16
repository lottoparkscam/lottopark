<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class CreditCardToken extends PaymentType {

	protected $paymentTypeKey = "creditcard";

	protected $required = ['cc_payload'];

	public function __construct($cc_payload = null){
		$this->fields = (object) [
			'cc_payload' => $cc_payload,
			'credit_card_trans_type' => null,
			'card_holder_name' => null,
        ];
	}

	public function setCCPayload ($value){
		$this->fields->cc_payload = $value;
	}

	public function setTransactionType ($value){
		$this->fields->credit_card_trans_type = $value;
	}

	public function setName ($value){
		$this->fields->card_holder_name = $value;
	}

	public function getCCPayload (){
		return $this->fields->cc_payload;
	}

	public function getTransactionType (){
		return $this->fields->credit_card_trans_type;
	}

	public function getName (){
		return $this->fields->card_holder_name;
	}

}

class CreditCard extends PaymentType {

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
			'cvv' => null,
			'order_id' => null,
			'exp_month' => null,
			'exp_year' => null,
			'credit_card_trans_type' => null,
			'remember_card' => null,
			'previous_order_id' => null
        ];
	}

	/* SETTER */
	public function setName ($value){
		$this->fields->card_holder_name = $value;
	}
	public function setNumber ($value){
		$this->fields->card_number = $value;
	}
	public function setCVV ($value){
		$this->fields->cvv = $value;
	}
	public function setOrderId ($value){
		$this->fields->order_id = $value;
	}
	public function setExpiryMonth ($value){
		$this->fields->exp_month = $value;
	}
	public function setExpiryYear ($value){
		$this->fields->exp_year = $value;
	}
	public function setTransactionType ($value){
		$this->fields->credit_card_trans_type = $value;
	}
	public function setRememberCardFlag ($value){
		$this->fields->remember_card = $value;
	}
	public function setLastOrderId ($value){
		$this->fields->previous_order_id = $value;
	}

	/* GETTERS */
	public function getName (){
		return $this->fields->card_holder_name;
	}
	public function getNumber (){
		return $this->fields->card_number;
	}
	public function getCVV (){
		return $this->fields->cvv;
	}
	public function getOrderId (){
		return $this->fields->order_id;
	}
	public function getExpiryMonth (){
		return $this->fields->exp_month;
	}
	public function getExpiryYear (){
		return $this->fields->exp_year;
	}
	public function getTransactionType (){
		return $this->fields->credit_card_trans_type;
	}
	public function getRememberCardFlag (){
		return $this->fields->remember_card;
	}
	public function getLastOrderId (){
		return $this->fields->previous_order_id;
	}

}

