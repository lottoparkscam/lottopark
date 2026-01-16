<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

require_once __DIR__."/Customer.php";
require_once __DIR__."/CustomerRetrieve.php";
require_once __DIR__."/CustomerUpdate.php";
require_once __DIR__."/CustomerCreate.php";
require_once __DIR__."/CustomerGetCards.php";

/***************
* Model
***************/
abstract class CustomerBase extends Model\BaseModel {

	protected $required = ['email'];

	public function __construct($email = null, $name = null){

		$this->fields = (object) [
			'name' => $name,
			'email' => $email
        ];

	}

	/* SETTERS */
	public function setName ($value){
		$this->fields->name = $value;
	}
	public function setEmailAddress ($value){
		$this->fields->email = $value;
	}

	/* GETTERS */
	public function getName (){
		return $this->fields->name;
	}
	public function getEmailAddress (){
		return $this->fields->email;
	}



}