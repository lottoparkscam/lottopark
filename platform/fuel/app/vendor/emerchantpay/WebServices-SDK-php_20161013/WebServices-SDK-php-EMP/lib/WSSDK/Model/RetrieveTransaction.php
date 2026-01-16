<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class RetrieveTransaction extends Model\BaseModel {

	public function __construct(){

		$this->fields = (object) [
			'trans_id' => null,
			'start_trans_id' => null,
			'date' => null,
			'include_risk_data' => null
        ];

	}

	protected function validate() {
		parent::validate();

		// make sure one of these is set
		if (!(
			isset($this->fields->trans_id) ||
			isset($this->fields->date)
		)) {
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ': One of the following is required [trans_id", "date"]', 0);
		}

		// make sure date is set if "trans_id" is set
		if (isset($this->fields->trans_id) && !isset($this->fields->date)) {
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ': date is required by "trans_id"', 0);
		}

	}


	/* SETTERS */
	public function setTansactionId ($value){
		$this->fields->trans_id = $value;
	}
	public function setStartTansactionId ($value){
		$this->fields->start_trans_id = $value;
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
	public function getTansactionId (){
		return $this->fields->trans_id;
	}
	public function getStartTansactionId (){
		return $this->fields->start_trans_id;
	}
	public function getDate (){
		return $this->fields->date;
	}
	public function isRiskDataIncluded (){
		return $this->fields->include_risk_data;
	}



}