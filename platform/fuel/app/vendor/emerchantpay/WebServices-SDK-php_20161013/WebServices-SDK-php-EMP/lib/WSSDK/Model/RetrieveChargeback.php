<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class RetrieveChargeback extends Model\BaseModel {

	private function __construct($chargeback_id, $trans_id, $date){

		$this->fields = (object) [
			'chargeback_id' => $chargeback_id,
			'trans_id' => $trans_id,
			'date' => $date
        ];

	}

	/* STATIC SETTERS */
	static function ByChargebackId ($value){
		return new self($value, null, null);
	}
	static function ByTransactionId ($value){
		return new self(null, $value, null);
	}
	static function ByDate ($value){

		if (!Model\BaseModel::isDateValid($value)){
			throw new \WSSDK\Model\ModelValidationException("retrieveChargeback: Date $value must match format YYYY-MM-DD", 1);
		}

		return new self(null, null, $value);
	}


	/* GETTERS */
	public function getChargebackId (){
		return $this->fields->chargeback_id;
	}
	public function getTansactionId (){
		return $this->fields->trans_id;
	}
	public function getDate (){
		return $this->fields->date;
	}



}