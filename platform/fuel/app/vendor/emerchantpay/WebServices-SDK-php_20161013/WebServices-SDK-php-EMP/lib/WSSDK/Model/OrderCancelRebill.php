<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderCancelRebill extends Model\BaseModel {

	protected $required = ['item_id'];

	public function __construct($item_id = null, $notify = null){

		$this->fields = (object) [
			'notify' => $notify,
			'item_id' => $item_id,
			'reason' => null
        ];

	}

	/* SETTERS */
	public function setNotify($value) { $this->fields->notify = $value; }
	public function setItemId($value) { $this->fields->item_id = $value; }
	public function setReason($value) { $this->fields->reason = $value; }


	/* GETTERS */
	public function getNotify() { return $this->fields->notify; }
	public function getItemId() { return $this->fields->item_id; }
	public function getReason() { return $this->fields->reason; }

}