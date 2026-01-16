<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class CreditCardVBV3D extends CreditCard {

	protected $paymentTypeKey = "creditcard";

	public function __construct(){
		parent::__construct();
	
		$this->required[] = 'eci';

		$this->fields->eci = null;
		$this->fields->xid = null;
		$this->fields->cavv = null;
		$this->fields->cavvAlgorithm = null;

	}

	/* SETTER */
	public function setEci ($value){
		$this->fields->eci = $value;
	}
	public function setXid ($value){
		$this->fields->xid = $value;
	}
	public function setCavv ($value){
		$this->fields->cavv = $value;
	}
	public function setCavvAlgorithm ($value){
		$this->fields->cavvAlgorithm = $value;
	}

	/* GETTER */
	public function getEci (){
		return $this->fields->eci;
	}
	public function getXid (){
		return $this->fields->xid;
	}
	public function getCavv (){
		return $this->fields->cavv;
	}
	public function getCavvAlgorithm (){
		return $this->fields->cavvAlgorithm;
	}

}

