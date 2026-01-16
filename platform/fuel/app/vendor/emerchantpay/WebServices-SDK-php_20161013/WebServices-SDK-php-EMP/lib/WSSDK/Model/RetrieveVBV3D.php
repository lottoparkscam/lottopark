<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class RetrieveVBV3D extends Model\BaseModel {

	protected $required = [
		'reference',
		'requestid'
    ];

	public function __construct(){

		$this->fields = (object) [
			'reference' => null,
			'requestid' => null
        ];

	}

	/* SETTERS */
	public function setReference($value) { $this->fields->reference = $value; }
	public function setRequestId($value) { $this->fields->requestid = $value; }

	/* GETTERS */
	public function getReference() { return $this->fields->reference; }
	public function getRequestId() { return $this->fields->requestid; }

}