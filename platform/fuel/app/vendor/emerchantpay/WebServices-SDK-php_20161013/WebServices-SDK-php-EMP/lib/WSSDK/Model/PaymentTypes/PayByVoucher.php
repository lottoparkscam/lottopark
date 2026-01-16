<?php

namespace WSSDK\Model\PaymentType;

use \WSSDK\Model as Model;

class PayByVoucher extends PaymentType {

	protected $paymentTypeKey = "genesispaybyvoucher";

	protected $required = ['customer_id_number', 'customer_bank_id', 'bank_account_number'];

	/**
	 * [Helper array of supported bank Ids to be used with the setCustomerBankId method]
	 * @var array
	 */
	static $BANK_IDS = [
		'ICBC' => ['ICBC', 'Industrial and Commercial Bank of China'],
		'CMBCHINA' => ['CMBCHINA', 'China Merchants Bank'],
		'ABC' => ['ABC', 'Agricultural Bank of China'],
		'CCB' => ['CCB', 'China Construction Bank'],
		'BCCB' => ['BCCB', 'Bank of Beijing'],
		'BOCO' => ['BOCO', 'Bank of Communications'],
		'CIB' => ['CIB', 'Industrial Bank'],
		'NJCB' => ['NJCB', 'Bank of Nanjing'],
		'CMBC' => ['CMBC', 'China Minsheng Banking Corp Ltd'],
		'CEB' => ['CEB', 'China Everbright Bank'],
		'BOC' => ['BOC', 'Bank of China'],
		'PINGANBANK' => ['PINGANBANK', 'Ping An Bank'],
		'HKBEA' => ['HKBEA', 'Bank of East Asia'],
		'NBCB' => ['NBCB', 'Bank of Ningbo'],
		'ECITIC' => ['ECITIC', 'China Citic Bank'],
		'SDB' => ['SDB', 'Shenzhen Development Bank'],
		'GDB' => ['GDB', 'Guangdong Development Bank'],
		'SHB' => ['SHB', 'Bank of Shanghai'],
		'SPDB' => ['SPDB', 'Shanghai Pudong Development Bank'],
		'POST' => ['POST', 'China Post'],
		'BJRCB' => ['BJRCB', 'BEIJING RURAL COMMERCIAL BANK'],
		'HXB' => ['HXB', 'Hua Xia Bank Co Ltd'],
		'HZBANK' => ['Hua Xia Bank Co Ltd', 'Bank of Hangzhou'],
		'SRCB' => ['SRCB', 'Shanghai Rural Commercial Bank']
    ];

	public function __construct(){
		$this->fields = (object) [
			'customer_id_number' => null,
			'customer_bank_id' => null,
			'bank_account_number' => null
        ];
	}

	/* SETTER */
	/**
	 * Customer ID number. Must be a 18 digits
	 * valid ID Card number/Resident Identity
	 * Card]. ISO 7064:1983
	 * @param Number $value
	 */
	public function setCustomerIdNumber ($value){
		$this->fields->customer_id_number = $value;
	}
	public function setCustomerBankId ($value){
		$this->fields->customer_bank_id = $value;
	}
	public function setBankAccountNumber ($value){
		$this->fields->bank_account_number = $value;
	}

	/* GETTERS */
	public function getCustomerIdNumber (){
		return $this->fields->customer_id_number;
	}
	public function getCustomerBankId (){
		return $this->fields->customer_bank_id;
	}
	public function getBankAccountNumber (){
		return $this->fields->bank_account_number;
	}

}