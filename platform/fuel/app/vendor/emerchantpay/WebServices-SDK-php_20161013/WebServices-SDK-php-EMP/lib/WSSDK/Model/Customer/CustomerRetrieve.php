<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class CustomerRetrieve extends Model\BaseModel {

	private function __construct($customer_id, $email, $start_creation_date, $end_creation_date){

		$this->fields = (object) [
			'customer_id' => $customer_id,
			'email' => $email,
			'start_creation_date' => $start_creation_date,
			'end_creation_date' => $end_creation_date
        ];

	}

	// static constructirs
	static function ByCustomerId($value){
		return new self($value, null, null, null);
	}
	static function ByEmailAddress ($value){
		return new self(null, $value, null, null);
	}
	static function ByDate ($start, $end){

		if (isset($start) && !Model\BaseModel::isDateValid($start)){
			throw new \WSSDK\Model\ModelValidationException("CustomerRetrieve: Date $start must match format YYYY-MM-DD", 1);
		}
		if (isset($end) && !Model\BaseModel::isDateValid($end)){
			throw new \WSSDK\Model\ModelValidationException("CustomerRetrieve: Date $end must match format YYYY-MM-DD", 1);
		}

		return new self(null, null, $start, $end);
	}

	/* GETTERS */
	public function getCustomerId (){
		return $this->fields->customer_id;
	}
	public function getEmailAddress (){
		return $this->fields->email;
	}
	public function getStartDate (){
		return $this->fields->start_creation_date;
	}
	public function getEndDate (){
		return $this->fields->end_creation_date;
	}



}