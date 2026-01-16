<?php
/**
  * @filesource
  */
namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderPayoutWebMoney extends OrderPayoutPaymentType {

	protected $paymentTypeKey = 'webmoney';
	protected $required = ['webmoney_payee_account_id'];

	public function __construct(){

		$this->fields = (object) [
			'currency' => null,
			'customer_email' => null,
			'webmoney_payee_account_id' => null
        ];

	}

	/* SETTERS */
	public function setCurrency($value) { $this->fields->currency = $value; }
	public function setEmail($value) { $this->fields->customer_email = $value; }
	public function setWebmoneyPayeeAccountId($value) { $this->fields->webmoney_payee_account_id = $value; }


	/* GETTERS */
	public function getCurrency() { return $this->fields->currency; }
	public function getAmount() { return $this->fields->customer_email; }
	public function getWebmoneyPayeeAccountId() { return $this->fields->webmoney_payee_account_id; }

}