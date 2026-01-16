<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/

class BlackListSearch extends ListSearch{};
class WhiteListSearch extends ListSearch{};

abstract class ListSearch extends Model\BaseModel {

	private function __construct($trans_id = null, $card_number = null, $email_address = null, $ip_address = null, $comment = null){

		$this->fields = (object) [
			'comment' => $comment,
			'trans_id' => $trans_id,
			'card_number' => $card_number,
			'email_address' => $email_address,
			'ip_address' => $ip_address
        ];

	}

	// static constructirs
	static function ByTransactionId ($value, $comment = null){
		return new static($value, null, null, null, $comment);
	}
	static function ByCardNumber ($value, $comment = null){
		return new static(null, $value, null, null, $comment);
	}
	static function ByEmailAddress ($value, $comment = null){
		return new static(null, null, $value, null, $comment);
	}
	static function ByIpAddress ($value, $comment = null){
		return new static(null, null, null, $value, $comment);
	}


	/* SETTERS */
	public function setComment ($value){
		$this->fields->comment = $value;
	}

	/* GETTERS */
	public function getComment (){
		return $this->fields->comment;
	}
	public function getTansactionId (){
		return $this->fields->trans_id;
	}
	public function getCardNumber (){
		return $this->fields->card_number;
	}
	public function getEmailAddress (){
		return $this->fields->email_address;
	}
	public function getIpAddress (){
		return $this->fields->ip_address;
	}



}