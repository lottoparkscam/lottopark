<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderVoid extends Model\BaseModel {

	protected $required = ['order_id'];

	public function __construct(){

		$this->fields = (object) [
			'notify' => null,
			'order_id' => null,
			'reason' => null
        ];

	}

	/* SETTERS */
	public function setNotify($value) { $this->fields->notify = $value; }
	public function setOrderId($value) { $this->fields->order_id = $value; }
	public function setReason($value) { $this->fields->reason = $value; }


	/* GETTERS */
	public function getNotify() { return $this->fields->notify; }
	public function getOrderId() { return $this->fields->order_id; }
	public function getReason() { return $this->fields->reason; }

}