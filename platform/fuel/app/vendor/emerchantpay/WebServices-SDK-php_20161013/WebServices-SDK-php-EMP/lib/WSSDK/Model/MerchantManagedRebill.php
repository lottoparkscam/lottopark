<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class MerchantManagedRebill extends Model\BaseModel {

	protected $required = ['order_id','item_id','amount','reference'];

	public function __construct(){

		$this->fields = (object) [
			'notify' => null,
			'order_id' => null,
			'item_id' => null,
			'amount' => null,
			'description' => null,
			'reference' => null
        ];

	}

	/* SETTERS */
	public function setNotify($value) { $this->fields->notify = $value; }
	public function setOrderId($value) { $this->fields->order_id = $value; }
	public function setItemId($value) { $this->fields->item_id = $value; }
	public function setAmount($value) { $this->fields->amount = $value; }
	public function setDescription($value) { $this->fields->description = $value; }
	public function setReference($value) { $this->fields->reference = $value; }



	/* GETTERS */
	public function getNotify() { return $this->fields->notify; }
	public function getOrderId() { return $this->fields->order_id; }
	public function getItemId() { return $this->fields->item_id; }
	public function getAmount() { return $this->fields->amount; }
	public function getDescription() { return $this->fields->description; }
	public function getReference() { return $this->fields->reference; }




}