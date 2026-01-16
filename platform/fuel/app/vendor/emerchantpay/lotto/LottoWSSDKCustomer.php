<?php
class LottoWSSDKCustomer extends \WSSDK\Model\Customer {
	protected $required = [
		'customer_email'
    ];
	public function __construct(){
		parent::__construct();
	}
}

?>