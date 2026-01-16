<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class Sofort extends PaymentType {

	protected $paymentTypeKey = "sofort";

	protected $required = [];

	public function __construct(){
		$this->fields = (object) [
			'sofort_bankcountry' => null,
			'sofort_bankcode' => null,
			'sofort_bankaccount' => null
        ];
	}

	static $SUPPORTED_COUNTRY_CODES = ['GB','DE', 'AT', 'CH', 'BE','NL','FR','ES','IT'];

	/* SETTER */

	/**
	 * The following countries are supported:
	 * 'GB','DE', 'AT', 'CH', 'BE','NL','FR',ES','IT'
	 * @param [type] $value [description]
	 */
	public function setBankCountry ($value){
		$this->fields->sofort_bankcountry = $value;
	}
	public function setBankCode ($value){
		$this->fields->sofort_bankcode = $value;
	}
	public function setBankAccount ($value){
		$this->fields->sofort_bankaccount = $value;
	}

	/* GETTERS */
	public function getBankId (){
		return $this->fields->sofort_bankcountry;
	}
	public function getBankCode (){
		return $this->fields->sofort_bankcode;
	}
	public function getBankAccount (){
		return $this->fields->sofort_bankaccount;
	}

}