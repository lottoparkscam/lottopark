<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class InPayGetInstructions extends Model\BaseModel {

	public function __construct($order_id){
		$this->fields = (object) [
			'order_id' => $order_id
        ];
	}

	/* GETTERS */
	public function getOrderId (){
		return $this->fields->order_id;
	}

}