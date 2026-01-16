<?php
/**
  * @filesource
  */
namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderPayoutUKash extends OrderPayoutPaymentType {

	protected $paymentTypeKey = 'ukash';

	public function __construct(){

		$this->fields = (object) [
			'currency' => null,
			'customer_email' => null
        ];

	}

	/* SETTERS */
	public function setCurrency($value) { $this->fields->currency = $value; }
	public function setEmail($value) { $this->fields->customer_email = $value; }

	/* GETTERS */
	public function getCurrency() { return $this->fields->currency; }
	public function getEmail() { return $this->fields->customer_email; }

}