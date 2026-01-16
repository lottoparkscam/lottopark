<?php

namespace WSSDK\Model;

use \WSSDK\Model as Model;

class DynamicDescriptor extends Model\BaseModel {

	protected $required = [];

	public function __construct(){

		$this->fields = (object) [
			'merchant_name' => null,
			'merchant_city' => null,
			'merchant_address' => null,
			'merchant_state' => null,
			'merchant_zip' => null,
			'merchant_country' => null
        ];

	}

	/* SETTERS */
	public function setName ($value){
		$this->fields->merchant_name = $value;
	}
	public function setCity ($value){
		$this->fields->merchant_city = $value;
	}
	public function setAddress ($value){
		$this->fields->merchant_address = $value;
	}
	public function setState ($value){
		$this->fields->merchant_state = $value;
	}
	public function setZipCode ($value){
		$this->fields->merchant_zip = $value;
	}
	public function setCountry ($value){
		$this->fields->merchant_country = $value;
	}

	/* GETTERS */
	public function getName (){
		return $this->fields->merchant_name;
	}
	public function getCity(){
		return $this->fields->merchant_city;
	}
	public function getAddress (){
		return $this->fields->merchant_address;
	}
	public function getState (){
		return $this->fields->merchant_state;
	}
	public function getZipCode (){
		return $this->fields->merchant_zip;
	}
	public function getCountry (){
		return $this->fields->merchant_country;
	}

}



