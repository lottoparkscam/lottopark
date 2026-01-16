<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class EZeeWallet extends PaymentType {

	protected $paymentTypeKey = "genesisezeewallet";

	protected $required = ['source_wallet_id', 'source_wallet_pwd'];

	public function __construct(){
		$this->fields = (object) [
			'source_wallet_id' => null,
			'source_wallet_pwd' => null
        ];
	}

	/* SETTER */
	public function setWalletId ($value){
		$this->fields->source_wallet_id = $value;
	}
	public function setWalletPassword ($value){
		$this->fields->source_wallet_pwd = $value;
	}

	/* GETTERS */
	public function getWalletId (){
		return $this->fields->source_wallet_id;
	}
	public function getWalletPassword (){
		return $this->fields->source_wallet_pwd;
	}

}