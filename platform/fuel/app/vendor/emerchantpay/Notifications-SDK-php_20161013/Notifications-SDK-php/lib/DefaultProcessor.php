<?php

namespace NotificationSDK;

/**
 * 
 */
class DefaultProcessor implements iProcessor {

    private $handleFunc;

    public function __construct($handleFunc){
        $this->handleFunc = $handleFunc;
    }

	public function process (iNotification $notification, $DEBUG) {
        call_user_func($this->handleFunc, $notification, $DEBUG);
        return $notification;
	}
}