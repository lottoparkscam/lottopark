<?php
class LottoWSSDKPreviousCreditCard extends \WSSDK\Model\PaymentType\CreditCard {
	protected $required = [
		'previous_order_id',
		'credit_card_trans_type',
		'cvv'
    ];
	public function __construct(){
		parent::__construct();
	}
}

?>