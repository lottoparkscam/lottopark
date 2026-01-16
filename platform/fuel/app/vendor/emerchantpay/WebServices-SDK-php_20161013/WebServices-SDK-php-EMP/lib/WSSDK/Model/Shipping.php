<?php

namespace WSSDK\Model;

use \WSSDK\Model as Model;

class Shipping extends Model\BaseModel {

	protected $required = [];

	public function __construct(){

		$this->fields = (object) [
			'shipping_first_name' => null,
			'shipping_last_name' => null,
			'shipping_company' => null,
			'shipping_address' => null,
			'shipping_address2' => null,
			'shipping_city' => null,
			'shipping_state' => null,
			'shipping_country' => null,
			'shipping_postcode' => null,
			'shipping_phone' => null
        ];

	}

	public function setFirstName ($value){
		$this->fields->shipping_first_name = $value;
	}
	public function setLastName ($value){
		$this->fields->shipping_last_name = $value;
	}
	public function setCompany ($value){
		$this->fields->shipping_company = $value;
	}
	public function setAddressLine1 ($value){
		$this->fields->shipping_first_name = $value;
	}
	public function setAddressLine2 ($value){
		$this->fields->shipping_address = $value;
	}
	public function setCity ($value){
		$this->fields->shipping_city = $value;
	}
	public function setState ($value){
		$this->fields->shipping_state = $value;
	}
	public function setCountry ($value){
		$this->fields->shipping_country = $value;
	}
	public function setPostcode ($value){
		$this->fields->shipping_postcode = $value;
	}
	public function setPhone ($value){
		$this->fields->shipping_phone = $value;
	}

	public function getFirstName (){
		return $this->fields->shipping_first_name;
	}
	public function getLastName (){
		return $this->fields->shipping_last_name;
	}
	public function getCompany (){
		return $this->fields->shipping_company;
	}
	public function getAddressLine1 (){
		return $this->fields->shipping_first_name;
	}
	public function getAddressLine2 (){
		return $this->fields->shipping_address;
	}
	public function getCity (){
		return $this->fields->shipping_city;
	}
	public function getState (){
		return $this->fields->shipping_state;
	}
	public function getCountry (){
		return $this->fields->shipping_country;
	}
	public function getPostcode (){
		return $this->fields->shipping_postcode;
	}
	public function getPhone (){
		return $this->fields->shipping_phone;
	}

}



