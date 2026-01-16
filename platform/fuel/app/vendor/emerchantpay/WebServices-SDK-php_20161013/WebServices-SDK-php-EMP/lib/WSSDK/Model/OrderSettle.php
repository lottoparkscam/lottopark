<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderSettle extends Model\BaseModel {

	protected $required = ['order_id'];

	public function __construct(){

		$this->fields = (object) [
			'notify' => null,
			'order_id' => null,
			'shipper_id' => null,
			'amount' => null,
			'track_id' => null
        ];

	}

	protected function validate(){
		parent::validate();
		if (isset($this->fields->track_id) !== isset($this->fields->shipper_id)){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ': track_id is required when shipper_id is set');
		}
	}

	/* SETTERS */
	public function setNotify($value) { $this->fields->notify = $value; }
	public function setOrderId($value) { $this->fields->order_id = $value; }
	public function setShipperId($value) { $this->fields->shipper_id = $value; }
	public function setAmount($value) { $this->fields->amount = $value; }
	public function setTrackId($value) { $this->fields->track_id = $value; }


	/* GETTERS */
	public function getNotify() { return $this->fields->notify; }
	public function getOrderId() { return $this->fields->order_id; }
	public function getShipperId() { return $this->fields->shipper_id; }
	public function getAmount() { return $this->fields->amount; }
	public function getTrackId() { return $this->fields->track_id; }




}