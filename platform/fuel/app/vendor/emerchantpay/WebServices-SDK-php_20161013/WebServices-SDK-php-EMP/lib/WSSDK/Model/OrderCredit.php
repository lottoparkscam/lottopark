<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
abstract class OrderCredit extends Model\BaseModel {

	protected $required = ['order_id', 'trans_id'];

	public function __construct(){

		$this->fields = (object) [
			'notify' => null,
			'order_id' => null,
			'trans_id' => null,
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


	/* GETTERS */
	public function getNotify() { return $this->fields->notify; }
	public function getOrderId() { return $this->fields->order_id; }
	public function getTransId() { return $this->fields->trans_id; }
	public function getReason() { return $this->fields->reason; }
	public function getReference() { return $this->fields->reference; }

}

class OrderFullCredit extends OrderCredit {

	public function __construct(){
		parent::__construct();
		$this->fields->amount = null;
		$this->required[] = 'amount';
	}

	public function setAmount($value) { $this->fields->amount = $value; }
	public function getAmount() { return $this->fields->amount; }

}

class OrderPartialCredit extends OrderCredit {

	protected $itemCount = 1;
	protected $idCountMap;
	public function __construct(){
		parent::__construct();
		$this->idCountMap = (object) [];
	}

	protected function validate() {
		parent::validate();
		if (Count(get_object_vars($this->idCountMap)) === 0){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ': At least one item must be added to credit a transaction');
		}
	}

	public function addItemCredit($id, $value) { 
		$this->idCountMap->$id = $this->itemCount;
		$this->fields->{'item_'.$this->itemCount.'_id'} = $value; 
		$this->fields->{'item_'.$this->itemCount.'_amount'} = $value; 
		$this->itemCount++; 
	}

	public function getItemCredit($id) { 
		$index = $this->idCountMap->$id;
		return $this->fields->{'item_'.$index.'_amount'};
	}

}