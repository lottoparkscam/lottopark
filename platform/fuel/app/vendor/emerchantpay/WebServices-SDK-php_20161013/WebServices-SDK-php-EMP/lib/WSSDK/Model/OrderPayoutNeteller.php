<?php
/**
  * @filesource
  */
namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderPayoutNeteller extends OrderPayoutPaymentType {

	protected $paymentTypeKey = 'neteller';
	protected $required = ['neteller_payout_account_id'];

	public function __construct(){

		$this->fields = (object) [
			'currency' => null,
			'customer_email' => null,
			'neteller_payout_account_id' => null
        ];

	}

	/* SETTERS */
	public function setCurrency($value) { $this->fields->currency = $value; }
	public function setEmail($value) { $this->fields->customer_email = $value; }
	public function setNetellerPayoutAccountId($value) { $this->fields->neteller_payout_account_id = $value; }


	/* GETTERS */
	public function getCurrency() { return $this->fields->currency; }
	public function getEmail() { return $this->fields->customer_email; }
	public function getNetellerPayoutAccountId() { return $this->fields->neteller_payout_account_id; }

}