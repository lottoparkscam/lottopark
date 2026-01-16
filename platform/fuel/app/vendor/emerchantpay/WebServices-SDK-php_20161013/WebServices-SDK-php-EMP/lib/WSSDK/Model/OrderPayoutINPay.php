<?php
/**
  * @filesource
  */
namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderPayoutINPay extends OrderPayoutPaymentType {

	protected $paymentTypeKey = 'inpay';

	protected $required = [
		'inpay_bank_country',
		'inpay_bank_name',
		'inpay_swift',
		'inpay_acc_number',
		'inpay_bank_address',
		'inpay_owner_name',
		'inpay_owner_address'
    ];

	public function __construct(){

		$this->fields = (object) [
			'inpay_bank_country' => null,
			'inpay_bank_name' => null,
			'inpay_swift' => null,
			'inpay_acc_number' => null,
			'inpay_bank_address' => null,
			'inpay_owner_name' => null,
			'inpay_owner_address' => null
        ];

	}

	/* SETTERS */
	public function setBankCountry($value) { $this->fields->inpay_bank_country = $value; }
	public function setBankName($value) { $this->fields->inpay_bank_name = $value; }
	public function setSwift($value) { $this->fields->inpay_swift = $value; }
	public function setAccNumber($value) { $this->fields->inpay_acc_number = $value; }
	public function setBankAddress($value) { $this->fields->inpay_bank_address = $value; }
	public function setOwnerName($value) { $this->fields->inpay_owner_name = $value; }
	public function setOwnerAddress($value) { $this->fields->inpay_owner_address = $value; }

	/* GETTERS */
	public function getBankCountry() { return $this->fields->inpay_bank_country; }
	public function getBankName() { return $this->fields->inpay_bank_name; }
	public function getSwift() { return $this->fields->inpay_swift; }
	public function getAccNumber() { return $this->fields->inpay_acc_number; }
	public function getBankAddress() { return $this->fields->inpay_bank_address; }
	public function getOwnerName() { return $this->fields->inpay_owner_name; }
	public function getOwnerAddress() { return $this->fields->inpay_owner_address; }

}