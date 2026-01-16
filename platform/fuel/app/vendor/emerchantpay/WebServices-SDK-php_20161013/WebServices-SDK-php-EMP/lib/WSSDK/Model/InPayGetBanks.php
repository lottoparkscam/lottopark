<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class InPayGetBanks extends Model\BaseModel {

	protected $required = ['country', 'currency'];

	public function __construct($country, $currency){
		$this->fields = (object) [
			'country' => $country,
			'currency' => $currency
        ];
	}

	/* GETTERS */
	public function getBankId (){
		return $this->fields->inpay_bank_id;
	}
	public function getOrderText (){
		return $this->fields->inpay_order_text;
	}

}