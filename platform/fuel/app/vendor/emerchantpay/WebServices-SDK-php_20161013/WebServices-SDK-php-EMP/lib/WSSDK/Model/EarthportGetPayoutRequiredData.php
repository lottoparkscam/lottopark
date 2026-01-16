<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class EarthportGetPayoutRequiredData extends Model\BaseModel {

	protected $required = ['currency', 'amount', 'country'];

	public function __construct(){
		$this->fields = (object) [
			'currency' => null,
			'amount' => null,
			'reference' => null,
			'language' => null,
			'country' => null
        ];
	}

	/* SETTER */
	public function setCurrency ($value){
		$this->fields->currency = $value;
	}
	public function setAmount ($value){
		$this->fields->amount = $value;
	}
	public function setReference ($value){
		$this->fields->reference = $value;
	}
	public function setLanguage ($value){
		$this->fields->language = $value;
	}
	public function setCountry ($value){
		$this->fields->country = $value;
	}

	/* GETTERS */
	public function getCurrency (){
		return $this->fields->currency;
	}
	public function getAmount (){
		return $this->fields->amount;
	}
	public function getReference (){
		return $this->fields->reference;
	}
	public function getLanguage (){
		return $this->fields->language;
	}
	public function getCountry (){
		return $this->fields->country;
	}

}