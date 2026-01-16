<?php

class LottoWSSDKOrderSubmit extends \WSSDK\Model\OrderSubmit {
	public function __construct(){
		parent::__construct();
		$this->fields->order_language = null;
		$this->fields->customer_email = null;
	}

	/* SETTERS */
	public function setOrderLanguage ($value) {
		$this->fields->order_language = $value;
	}
	public function setCustomerEmail ($value) {
		$this->fields->customer_email = $value;
	}
	/* GETTERS */
	public function getOrderLanguage () {
		return $this->fields->order_language;
	}
	public function getCustomerEmail () {
		return $this->fields->customer_email;
	}
}

?>