<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class RetrieveOrder extends Model\BaseModel {

	public function __construct(){

		$this->fields = (object) [
			'order_id' => null,
			'trans_id' => null,
			'rebill_id' => null,
			'order_reference' => null,
			'date' => null,
			'include_risk_data' => null
        ];

	}

	protected function validate() {
		parent::validate();

		// make sure one of these is set
		if (!(
			isset($this->fields->order_id) ||
			isset($this->fields->trans_id) ||
			isset($this->fields->rebill_id) ||
			isset($this->fields->order_reference) ||
			isset($this->fields->date)
		)) {
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ': One of the following is required ["order_id", "trans_id", "rebill_id", "order_reference", "date"]', 0);
		}

		// make sure date is set if "order_id", "trans_id", "rebill_id" are set
		if ((
				isset($this->fields->order_id) ||
				isset($this->fields->trans_id) ||
				isset($this->fields->rebill_id)
			) && !isset($this->fields->date)
		) {
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ': date is required for the following fields ["order_id", "trans_id", "rebill_id"]', 0);
		}

	}


	/* SETTERS */
	public function setOrderId ($value){
		$this->fields->order_id = $value;
	}
	public function setTansactionId ($value){
		$this->fields->trans_id = $value;
	}
	public function setRebillId ($value){
		$this->fields->rebill_id = $value;
	}
	public function setOrderReference ($value){
		$this->fields->order_reference = $value;
	}
	public function setDate ($value){

		if (!Model\BaseModel::isDateValid($value)){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": Date $value must match format YYYY-MM-DD", 1);
		}

		$this->fields->date = $value;
	}
	public function setRiskDataIncluded ($value){
		$this->fields->include_risk_data = $value;
	}


	/* GETTERS */
	public function getOrderId (){
		return $this->fields->order_id;
	}
	public function getTansactionId (){
		return $this->fields->trans_id;
	}
	public function getRebillId (){
		return $this->fields->rebill_id;
	}
	public function getOrderReference (){
		return $this->fields->order_reference;
	}
	public function getDate (){
		return $this->fields->date;
	}
	public function isRiskDataIncluded (){
		return $this->fields->include_risk_data;
	}



}