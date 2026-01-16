<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class CustomerUpdate extends CustomerBase {

	protected $required = ['customer_id'];

	public function __construct($customer_id = null, $email = null, $name = null){
		parent::__construct($email, $name);
		$this->fields->customer_id = $customer_id;
	}

	protected function validate(){
		parent::validate();

		if(!isset($this->fields->email) && !isset($this->fields->name)){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ': Set at least email or name to update this customer.', 1);
		}

	}

	/* SETTERS */
	public function setCustomerId ($value){
		$this->fields->customer_id = $value;
	}

	/* GETTERS */
	public function getCustomerId (){
		return $this->fields->customer_id;
	}

}