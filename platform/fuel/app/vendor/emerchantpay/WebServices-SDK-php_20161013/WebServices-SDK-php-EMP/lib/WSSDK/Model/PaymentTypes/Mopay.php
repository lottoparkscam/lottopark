<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class Mopay extends PaymentType {

	protected $paymentTypeKey = "mopay";

	protected $required = ['customer_country', 'mopay_productname'];

	public function __construct(){
		$this->fields = (object) [
			'customer_country' => null,
			'mopay_productname' => null
        ];
	}

	/* SETTER */
	public function setCustomerCountry ($value){
		$this->fields->customer_country = $value;
	}
	public function setProductName ($value){
		$this->fields->mopay_productname = $value;
	}

	/* GETTERS */
	public function getCustomerCountry (){
		return $this->fields->customer_country;
	}
	public function getProductName (){
		return $this->fields->mopay_productname;
	}

}