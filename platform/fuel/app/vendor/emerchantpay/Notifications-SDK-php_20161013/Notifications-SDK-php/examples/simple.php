<?php

// Require the NotificationRouter and the classes used
require __DIR__.'/../lib/NotificationRouter.php';
use \NotificationSDK\NotificationRouter as NotificationRouter;
use \NotificationSDK\iNotification as iNotification;
use \NotificationSDK\DefaultProcessor as DefaultProcessor;


// This code will run for all notification request after the signature of the request is validated.
NotificationRouter::RouteProcess('*', new DefaultProcessor(

	// all notifications will be handled by this function
	function(iNotification $notification, $DEBUG){
		// get the data from the notification
		$data = $notification->getData();
		switch ($notification->getType()) {
			case 'credit':
			
				// Add your own custom logic here to store the notification data, or perform updates on your system.
			    // Refer to the Web Service API or Payment Form API documentation for a list of all fields returned.
			    // Example fields:

				$transId	= $data["trans_id"];
				$amount		= $data["amount"];

				break;

			default:
				# code...
				break;
		}

	})
);



// create handler singleton with secret key
$handler = NotificationRouter::GetNotificationHandler('1234');


// Handle the post request data
if($_POST) {

	try{
		// run the handler with the post data, pass through and debug flags,
		// This will run the code you have written in the DefaultProcessor function above
		$handler->handle($_POST, true, true);
		// respond 200 "ok"
		echo "OK";
	}
	catch (\Exception $ex){

		// Handle your errors here

		// respond 200 "ok"
		echo "OK";
	}

}

