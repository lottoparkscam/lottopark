<?php
/**
  * @filesource
  */
namespace WSSDK\Model;
use \WSSDK\Model as Model;

/***************
* Order Model
***************/
class OrderSubmit extends Model\BaseModel {

	protected $required = ['order_reference', 'order_currency'];

	public function __construct(){

		$this->fields = (object) [
			'order_reference'	=> null,
			'order_currency'	=> null,
			'payment_type'		=> null,
			'ip_address'		=> null,
			'pass_through'		=> null,
			'realm_username'	=> null,
			'realm_password'	=> null,
			'customer_id'		=> null,
			'moto'				=> null,
			'notify'			=> null
        ];

	}

	/* SETTERS */
	public function setOrderReference ($value){
		$this->fields->order_reference = $value;
	}
	public function setCurrency ($value){
		$this->fields->order_currency = $value;
	}
	public function setIpAddress ($value){
		$this->fields->ip_address = $value;
	}
	public function setPaymentType ($value){
		$this->fields->payment_type = $value;
	}
	public function setPassThrough ($value){
		$this->fields->pass_through = $value;
	}
	public function setRealmUserName ($value){
		$this->fields->realm_username = $value;
	}
	public function setRealmPassword ($value){
		$this->fields->realm_password = $value;
	}
	public function setCustomerId ($value){
		$this->fields->customer_id = $value;
	}
	public function setMoto ($value){
		$this->fields->moto = $value;
	}
	public function setNotify ($value){
		$this->fields->notify = $value;
	}

	/* GETTERS */
	public function getOrderReference (){
		return $this->fields->order_reference;
	}
	public function getCurrency (){
		return $this->fields->order_currency;
	}
	public function getIpAddress (){
		return $this->fields->ip_address;
	}
	public function getPaymentType (){
		return $this->fields->payment_type;
	}
	public function getPassThrough (){
		return $this->fields->pass_through;
	}
	public function getRealmUserName (){
		return $this->fields->realm_username;
	}
	public function getRealmPassword (){
		return $this->fields->realm_password;
	}
	public function getCustomerId (){
		return $this->fields->customer_id;
	}
	public function getMoto (){
		return $this->fields->moto;
	}
	public function getNotify ($value){
		return $this->fields->notify;
	}

}

