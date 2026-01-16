<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class Infin extends PaymentType {

	protected $paymentTypeKey = "infin";

	public function __construct(){
		$this->fields = (object) [
			'infin_order_text' => null
        ];
	}

	/* SETTER */
	public function setDescription ($value){
		$this->fields->infin_order_text = $value;
	}

	/* GETTERS */
	public function getDescription (){
		return $this->fields->infin_order_text;
	}

}