<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class UATP extends PaymentType {

	protected $paymentTypeKey = "uatp";

	protected $required = ['airlineData'];

	public function __construct(Model\AirlineDataUATP $airlineData){

		$this->fields = (object) [
			'airlineData' => $airlineData
        ];

	}

	/**
	 * validate the required airline data field
	 * @return Boolean [description]
	 */
	protected function validate() {
		parent::validate();
		$this->fields->airlineData->validate();
		return true;
	}

	/**
	 * serialise the required airline data field
	 * @return String [description]
	 */
	protected function formSerialize() {
		$encoded = $this->fields->airlineData->formSerialize();
		return $encoded;
	}

}