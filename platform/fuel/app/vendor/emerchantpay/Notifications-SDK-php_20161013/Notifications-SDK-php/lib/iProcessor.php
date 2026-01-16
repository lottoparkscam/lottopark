<?php

namespace NotificationSDK;

/**
 * iProcessor interface. A Processor is used to handle one or more notification
 * requests. These are designed to be black boxes by nature and should not be reliant on other processors
 */
interface iProcessor {
	/**
	 * The process method receives the notification and does what it will with it.
	 * @param  iNotification $notification The notification data to process
	 * @return iNotification
	 */
	public function process (iNotification $notification, $DEBUG);
}