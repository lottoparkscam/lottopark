<?php

namespace NotificationSDK;

/**
 * interface for Notifications
 */
interface iNotification {
	/**
	 * Get the notification_type from the request params
	 * @return String
	 */
	public function getType ();
	/**
	 * Return all key value pairs from the request params
	 * @return Array<String, Any>
	 */
	public function getData ();
}

/**
 * implamentation of iNotification, a wrapper class for passing arround
 * the notifiction request data
 */
class Notification implements iNotification {

	/**
	 * Internal storage of key value 
	 * @var Array<String, Any>
	 */
	private $hashMap;

	static public function FromFormEncodedString($string){
		$data;
		parse_str($string, $data);
		return new self($data);
	}

	static public function FromKeyValueArray($array){
		return new self($array);
	}

	/**
	 * wrap the form parameters on an iNotification class
	 * @param String $data The url Encode string of parameters from the request
	 */
	private function __construct($data){

		$this->hashMap = $data;
		if(!array_key_exists('notification_type',$this->hashMap)){
			throw new \Exception("required field notification_type not found", 1);
		}

	}

	public function getType () {
		return $this->hashMap['notification_type'];
	}
	public function getData () {
		return $this->hashMap;
	}
}