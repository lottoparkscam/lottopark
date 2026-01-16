<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class Raberil extends PaymentType {

	protected $paymentTypeKey = "raberil";

	protected $required = [
		'raberil_identity_info',
		'customer_country',
		'order_currency',
		'customer_first_name',
		'customer_last_name',
		'customer_state',
		'customer_city',
		'customer_address',
		'customer_email'
    ];

	public function __construct(){
		$this->fields = (object) [
			'raberil_identity_info' => null,
			'raberil_provider' => null,
			'customer_country' => null,
			'order_currency' => null,
			'customer_first_name' => null,
			'customer_last_name' => null,
			'customer_state' => null,
			'customer_city' => null,
			'customer_address' => null,
			'customer_email' => null
        ];
	}

	/* SETTER */
	public function setIdentityInfo ($value){
		$this->fields->raberil_identity_info = $value;
	}
	public function setProvider($value){
		$this->fields->raberil_provider = $value;
	}
	public function setCustomerCountry ($value){
		$this->fields->customer_country = $value;
	}
	public function setCurrency ($value){
		$this->fields->order_currency = $value;
	}
	public function setCustomerFirstName($value){
		$this->fields->customer_first_name = $value;
	}
	public function setCustomerLastName ($value){
		$this->fields->customer_last_name = $value;
	}
	public function setCustomerState ($value){
		$this->fields->customer_state = $value;
	}
	public function setCustomerCity ($value){
		$this->fields->customer_city = $value;
	}
	public function setCustomerAddress($value){
		$this->fields->customer_address = $value;
	}
	public function setCustomerEmail ($value){
		$this->fields->customer_email = $value;
	}

	/* GETTERS */
	public function getIdentityInfo (){
		return $this->fields->raberil_identity_info;
	}
	public function getProvider(){
		return $this->fields->raberil_provider;
	}
	public function getCustomerCountry (){
		return $this->fields->customer_country;
	}
	public function getCurrency (){
		return $this->fields->order_currency;
	}
	public function getCustomerFirstName(){
		return $this->fields->customer_first_name;
	}
	public function getCustomerLastName (){
		return $this->fields->customer_last_name;
	}
	public function getCustomerState (){
		return $this->fields->customer_state;
	}
	public function getCustomerCity (){
		return $this->fields->customer_city;
	}
	public function getCustomerAddress(){
		return $this->fields->customer_address;
	}
	public function getCustomerEmail (){
		return $this->fields->customer_email;
	}

}