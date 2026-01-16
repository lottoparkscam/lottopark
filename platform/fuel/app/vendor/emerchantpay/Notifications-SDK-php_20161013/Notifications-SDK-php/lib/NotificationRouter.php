<?php

namespace NotificationSDK;

require_once __DIR__."/iNotification.php";
require_once __DIR__."/iProcessor.php";
require_once __DIR__."/SignitureValidationProcessor.php";
require_once __DIR__."/DefaultProcessor.php";

class HandlerException extends \Exception {}

/**
 * This class is responsible for mapping processes to notification types.
 * It provides static methods for managing a single collection of process routes.
 * And a singleton instance responsible for the handling pontifications and running the correct processor.
 */
class NotificationRouter {

	/* =============== STATIC ================= */

	/**
	 * Static map of notification types -> Array<iProcessor>
	 * @var Array<string, Array<iProcessor>>
	 */
	static private $processorMap = [
		'*' => []
    ];

	/**
	 * Static singleton instance
	 * @var null
	 */
	static private $instance = null;

	/**
	 * This methods is responsible for creating and managing the singleton instance
	 * It will return a NotificationRouter instance capable of handling a notification.
	 *
	 * @param String $signatureSecret The singleton must be created with the merchant's secret for validating signatures.
	 * Once created, the singleton can be accessed by calling the method with zero parameters
	 */
	static public function GetNotificationHandler($signatureSecret = null){

		// singleton guard
		if (isset(self::$instance)){
			if (isset($signatureSecret)){
				throw new HandlerException("NotificationRouter singleton instance has already been created, parameter 'signatureSecret' not allowed", 1);
			} else {
				return self::$instance;
			}
		} else {
			if (isset($signatureSecret) === false){
				throw new HandlerException("NotificationRouter singleton instance requires the 'signatureSecret' parameter to be created ", 1);
			}
		}

		self::$instance = new self($signatureSecret);
		return self::$instance;
	}

	/**
	 * Adds a process to the route table, to be match to the provided key
	 * @param  String     $typeKey   Notification type string to match, use '*' to catch all notification types
	 * @param  iProcessor $processor iProcessor to route to
	 * @return void
	 */
	static public function RouteProcess($typeKey, iProcessor $processor){

		// if (isset(self::$instance)){
		// 	throw new HandlerException("registraction is not possible after handler instance is created", 1);
		// }

		// test Map ProcessorMap for key. if key exist add to Stack. else create key
		$typeKeyExists = array_key_exists($typeKey, self::$processorMap);

		if(!$typeKeyExists){
			self::$processorMap[$typeKey] = [$processor];
		} else {
			self::$processorMap[$typeKey][] = $processor;
		}

	}

	/**
	 * Returns a list of processors that match the notification type of the iNotification passed in
	 * @param  iNotification $notification
	 * @param  Boolean        $allowIfOnlyCatchAll
	 * @return Array<iProcessor>
	 */
	static public function MatchProcessors(iNotification $notification, $allowIfNotMatched){

		$processorList = self::$processorMap['*'];
		$typeKey = $notification->getType();

		$typeKeyExists = array_key_exists($typeKey, self::$processorMap);
		if($typeKeyExists){
			$processorList = array_merge($processorList, self::$processorMap[$typeKey]);
		} else if ($allowIfNotMatched === false) {
			throw new HandlerException("No processors found for notification type \"$typeKey\"", 1);
		}

		return $processorList;

	}


	/* =============== INSTANCE ================= */

	/**
	 * Private constructor for creating singleton
	 * @param String $signatureSecret
	 */
	private function __construct($signatureSecret){
		// add to beginning of catch all process queue
		array_unshift(self::$processorMap['*'], new SignitureValidationProcessor($signatureSecret));
	}

	/**
	 * The handle method takes the form body data and process it using the processors
	 * added to the route collection by the user. The final notification is returned as a convenience, any type specific processes should be handled by the processor
	 * @param String  $Data              Url Form Encoded string
	 * @param boolean $allowIfNotMatched If True, an exception will not be thrown if the specific notification type is not matched. Any catch all processors will still be run.
	 * @return iNotification
	 */
	public function handle($data, $allowIfNotMatched = false, $DEBUG = false) {

		/* iNotification */
		$notification = $this->parse($data);

		/* Stack<iProcessor> */
		$processors = self::matchProcessors($notification, $allowIfNotMatched);

		forEach($processors as $proc){
			$n = $proc->process($notification, $DEBUG);
			$notification = is_null($n) ? $notification : $n;
		}

		return $notification;

	}

	/**
	 * Take the string and convert it to a array of key value pears
	 * @param  String  $Data              Url Form Encoded string
	 * @return Array<String, String>
	 */
	private function parse($Data){
		// Do we need to handle encoded messages ??
		switch (gettype($Data)) {
			case 'string':
				return Notification::FromFormEncodedString($Data);
				break;
			case 'array':
				return Notification::FromKeyValueArray($Data);
				break;
			default:
				throw new \Exception("Unable to handle data of type:" . gettype($Data) . ". Expect key value string or array.", 1);
				break;
		}
	}

}