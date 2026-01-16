<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class PhoneVerify extends Model\BaseModel {

	protected $required = [
		'ip_address', 'phone_sms', 'country_code', 'phone_number'
    ];

	private function __construct($email, $ccfirst6, $cclast4){

		$this->fields = (object) [
			'ip_address' => null,
			'email' => $email,
			'ccfirst6' => $ccfirst6,
			'cclast4' => $cclast4,
			'phone_sms' => null,
			'country_code' => null,
			'phone_number' => null,
			'area_code_zero' => null,
			'ext_number' => null,
			'ext_type' => null
        ];

	}

	/* STATIC SETTERS */
	static function ByEmail ($email){
		return new self($email, null, null);
	}
	/**
	 * [ByCreditcard description]
	 * @param number $ccfirst6 first 6 digits of creditcard number
	 * @param number $cclast4  last 4 digits of creditcard number
	 */
	static function ByCreditcard($ccfirst6, $cclast4){
		return new self(null, $ccfirst6, $cclast4);
	}

	/* SETTERS */
	public function setIpAddress($value){
		$this->fields->ip_address = $value;
	}
	/**
	 * expected values are "phone" or "sms"
	 * @param String $value
	 */
	public function setPhoneSms($value){
		$this->fields->phone_sms = $value;
	}
	public function setCountryCode($value){
		$this->fields->country_code = $value;
	}
	public function setPhoneNumber($value){
		$this->fields->phone_number = $value;
	}
	public function setAreaCodeZero($value){
		$this->fields->area_code_zero = $value;
	}
	/**
	 * US ONLY
	 * @param string $value
	 */
	public function setExtNumber($value){
		$this->fields->ext_number = $value;
	}
	/**
	 * Expected options
	 * "1" – PBX
     * "2" – Live Operator
	 * @param number $value
	 */
	public function setExtType($value){
		$this->fields->ext_type = $value;
	}

	/* GETTERS */
	public function getEmail(){
		return $this->fields->email;
	}
	public function getCreditcard(){
		return $this->fields->ccfirst6 . '...' . $this->fields->cclast4;
	}
	public function getIpAddress(){
		return $this->fields->ip_address;
	}
	public function getPhoneSms(){
		return $this->fields->phone_sms;
	}
	public function getCountryCode(){
		return $this->fields->country_code;
	}
	public function getPhoneNumber(){
		return $this->fields->phone_number;
	}
	public function getAreaCodeZero(){
		return $this->fields->area_code_zero;
	}
	public function getExtNumber(){
		return $this->fields->ext_number;
	}
	public function getExtType(){
		return $this->fields->ext_type;
	}



}