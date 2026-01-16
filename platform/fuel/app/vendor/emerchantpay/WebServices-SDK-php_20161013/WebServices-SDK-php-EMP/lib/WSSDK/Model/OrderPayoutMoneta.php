<?php
/**
  * @filesource
  */
namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderPayoutMoneta extends OrderPayoutPaymentType {

	protected $paymentTypeKey = 'monetas2s';
	protected $required = ['monetas2s_payer_id'];

	public function __construct(){

		$this->fields = (object) [
			'monetas2s_payer_id' => null
        ];

	}

	/* SETTERS */
	public function setMonetas2sPayerId($value) { $this->fields->monetas2s_payer_id = $value; }

	/* GETTERS */
	public function getMonetas2sPayerId() { return $this->fields->monetas2s_payer_id; }

}