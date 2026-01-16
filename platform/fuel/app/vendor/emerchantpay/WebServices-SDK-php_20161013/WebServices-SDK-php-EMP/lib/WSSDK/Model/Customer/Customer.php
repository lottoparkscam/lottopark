<?php

namespace WSSDK\Model;

use \WSSDK\Model as Model;

class Customer extends Model\BaseModel {

	protected $required = [
		'customer_first_name',
		'customer_last_name',
		'customer_email'
    ];

	public function __construct(){

		$this->fields = (object) [
			'customer_first_name' => null,
			'customer_last_name' => null,
			'customer_company' => null,
			'customer_address' => null,
			'customer_address2' => null,
			'customer_city' => null,
			'customer_state' => null,
			'customer_country' => null,
			'customer_postcode' => null,
			'customer_phone' => null,
			'customer_email' => null,
			'create_customer'	=> 1
        ];

	}


	public function setFirstName ($value){
		$this->fields->customer_first_name = $value;
	}
	public function setLastName ($value){
		$this->fields->customer_last_name = $value;
	}
	public function setCompany ($value){
		$this->fields->customer_company = $value;
	}
	public function setAddressLine1 ($value){
		$this->fields->customer_address = $value;
	}
	public function setAddressLine2 ($value){
		$this->fields->customer_address2 = $value;
	}
	public function setCity ($value){
		$this->fields->customer_city = $value;
	}
	public function setState ($value){
		$this->fields->customer_state = $value;
	}
	public function setCountry ($value){
		$this->fields->customer_country = $value;
	}
	public function setPostcode ($value){
		$this->fields->customer_postcode = $value;
	}
	public function setPhone ($value){
		$this->fields->customer_phone = $value;
	}
	public function setEmail ($value){
		$this->fields->customer_email = $value;
	}

	public function getFirstName (){
		return $this->fields->customer_first_name;
	}
	public function getLastName (){
		return $this->fields->customer_last_name;
	}
	public function getCompany (){
		return $this->fields->customer_company;
	}
	public function getAddressLine1 (){
		return $this->fields->customer_address;
	}
	public function getAddressLine2 (){
		return $this->fields->customer_address2;
	}
	public function getCity (){
		return $this->fields->customer_city;
	}
	public function getState (){
		return $this->fields->customer_state;
	}
	public function getCountry (){
		return $this->fields->customer_country;
	}
	public function getPostcode (){
		return $this->fields->customer_postcode;
	}
	public function getPhone (){
		return $this->fields->customer_phone;
	}
	public function getEmail (){
		return $this->fields->customer_email;
	}

}
