<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderDirectCFT extends Model\BaseModel {

	protected $required = ['amount', 'currency'];

	public function __construct(){

		$this->fields = (object) [
			'notify' => null,
			'currency' => null,
			'amount' => null,
			'language' => null,
			'reference' => null
        ];

	}

	/* SETTERS */
	public function setNotify($value) { $this->fields->notify = $value; }
	public function setCurrency ($value){ $this->fields->currency = $value; }
	public function setLanguage($value) { $this->fields->language = $value; }
	public function setReference($value) { $this->fields->reference = $value; }
	public function setAmount($value) { $this->fields->amount = $value; }


	/* GETTERS */
	public function getNotify() { return $this->fields->notify; }
	public function getCurrency() { return $this->fields->currency; }
	public function getLanguage() { return $this->fields->language; }
	public function getReference() { return $this->fields->reference; }
	public function getAmount() { return $this->fields->amount; }

}
