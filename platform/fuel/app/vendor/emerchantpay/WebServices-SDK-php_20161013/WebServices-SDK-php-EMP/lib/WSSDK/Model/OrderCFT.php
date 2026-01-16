<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderCFT extends Model\BaseModel {

	protected $required = ['order_id', 'trans_id', 'amount'];

	public function __construct(){

		$this->fields = (object) [
			'notify' => null,
			'order_id' => null,
			'trans_id' => null,
			'amount' => null,
			'reason' => null,
			'reference' => null
        ];

	}

	/* SETTERS */
	public function setNotify($value) { $this->fields->notify = $value; }
	public function setOrderId($value) { $this->fields->order_id = $value; }
	public function setTransId($value) { $this->fields->trans_id = $value; }
	public function setReason($value) { $this->fields->reason = $value; }
	public function setReference($value) { $this->fields->reference = $value; }
	public function setAmount($value) { $this->fields->amount = $value; }


	/* GETTERS */
	public function getNotify() { return $this->fields->notify; }
	public function getOrderId() { return $this->fields->order_id; }
	public function getTransId() { return $this->fields->trans_id; }
	public function getReason() { return $this->fields->reason; }
	public function getReference() { return $this->fields->reference; }
	public function getAmount() { return $this->fields->amount; }

}
