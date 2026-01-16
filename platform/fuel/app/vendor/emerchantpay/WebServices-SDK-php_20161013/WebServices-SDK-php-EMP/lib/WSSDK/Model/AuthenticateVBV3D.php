<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class AuthenticateVBV3D extends Model\BaseModel {

	protected $required = [
		'reference',
		'callback_url',
		'amount',
		'currency',
		'browser_useragent',
		'browser_accept'
    ];

	public function __construct(){

		$this->fields = (object) [
			'reference' => null,
			'callback_url' => null,
			'cc_payload' => null,
			'cardnumber' => null,
			'expdate' => null,
			'amount' => null,
			'currency' => null,
			'browser_useragent' => null,
			'browser_accept' => null
        ];

	}

	/* SETTERS */
	public function setReference($value) { $this->fields->reference = $value; }
	public function setCallbackUrl($value) { $this->fields->callback_url = $value; }
	public function setCCPayload($value) { $this->fields->cc_payload = $value; }
	public function setCardNumber($value) { $this->fields->cardnumber = $value; }
	public function setExpdate($value) {
		if (!Model\BaseModel::isDateValid($value, 'ym')){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": setExpdate must match format YYMM", 1);
		}
		$this->fields->expdate = $value;
	}
	public function setAmount($value) { $this->fields->amount = $value; }
	public function setCurrency($value) { $this->fields->currency = $value; }
	public function setBrowserUserAgent($value) { $this->fields->browser_useragent = $value; }
	public function setBrowserAccept($value) { $this->fields->browser_accept = $value; }

	/* GETTERS */
	public function getReference() { return $this->fields->reference; }
	public function getCallbackUrl() { return $this->fields->callback_url; }
	public function getCCPayload() { return $this->fields->cc_payload; }
	public function getCardNumber() { return $this->fields->cardnumber; }
	public function getExpdate() { return $this->fields->expdate; }
	public function getAmount() { return $this->fields->amount; }
	public function getCurrency() { return $this->fields->currency; }
	public function getBrowserUserAgent($value) { return $this->fields->browser_useragent; }
	public function getBrowserAccept($value) { return $this->fields->browser_accept; }




}