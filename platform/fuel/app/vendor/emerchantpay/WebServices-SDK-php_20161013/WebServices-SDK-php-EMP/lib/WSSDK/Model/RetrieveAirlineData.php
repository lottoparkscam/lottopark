<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class RetrieveAirlineData extends Model\BaseModel {

	public function __construct(){

		$this->fields = (object) [
			'trans_id' => null,
			'date' => null
        ];

	}

	protected function validate(){

		parent::validate();

		if (
			!isset($this->fields->trans_id) &&
			!isset($this->fields->date)
		) {
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": at least trans_id or date must be set.", 1);
		}

	}

	/* SETTERS */
	public function setTransactionId($value) { $this->fields->trans_id = $value; }
	public function setDate($value) {

		if (!is_null($value) && !Model\BaseModel::isDateValid($value)){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": setDate must match format YYYY-MM-DD", 1);
		}
		$this->fields->date = $value;
	}


	/* GETTERS */
	public function getTransactionId() { return $this->fields->trans_id; }
	public function getDate() { return $this->fields->date; }

}