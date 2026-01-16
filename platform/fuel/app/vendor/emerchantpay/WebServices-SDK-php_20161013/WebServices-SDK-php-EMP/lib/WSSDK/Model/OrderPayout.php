<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderPayout extends Model\BaseModel {

	protected $required = ['order_id', 'amount', 'payment_type'];

	public function __construct(){

		$this->fields = (object) [
			'notify' => null,
			'amount' => null,
			'order_id' => null,
			'reference' => null,
			'payment_type' => null
        ];

	}

	/* SETTERS */
	public function setNotify($value) { $this->fields->notify = $value; }
	public function setAmount($value) { $this->fields->amount = $value; }
	public function setOrderId($value) { $this->fields->order_id = $value; }
	public function setReference($value) { $this->fields->reference = $value; }
	public function setPaymentType($value) { $this->fields->payment_type = $value; }


	/* GETTERS */
	public function getNotify() { return $this->fields->notify; }
	public function getAmount() { return $this->fields->amount; }
	public function getOrderId() { return $this->fields->order_id; }
	public function getReference() { return $this->fields->reference; }
	public function getPaymentType() { return $this->fields->payment_type; }

}