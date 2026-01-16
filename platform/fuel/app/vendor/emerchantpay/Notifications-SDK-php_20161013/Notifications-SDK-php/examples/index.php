<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

use \NotificationSDK\NotificationRouter as NotificationRouter;
use \NotificationSDK\iProcessor as iProcessor;
use \NotificationSDK\iNotification as iNotification;

require  __DIR__."/../lib/NotificationRouter.php";

// logging constants
if(!defined('STDIN'))  define('STDIN',  fopen('php://stdin',  'r'));
if(!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'w'));
if(!defined('STDERR')) define('STDERR', fopen('php://stderr', 'w'));

// define processors
class logProcess implements iProcessor {
	public function process(iNotification $notification, $DEBUG){
		fputs(STDOUT, "LOG: Notification Type: ".$notification->getType()."\n");
	}
}
// add processor to catch all route
NotificationRouter::RouteProcess('*', new logProcess());

class VoidOrderProcess implements iProcessor {
	public function process(iNotification $notification, $DEBUG){
		// get the data from the notification
		$voidOrder = $notification->getData();

		// Add your own custom logic here to store the notification data, or perform updates on your system.
        // Refer to the Web Service API or Payment Form API documentation for a list of all fields returned.
        // Example fields:

		// as en example here we are logging the amount of the order voided.
		fputs(STDOUT, "LOG: Void Order Logged: ".$notification->getType()." amount " .$voidOrder["amount"]."\n");
	}
}
// add processor to void route
NotificationRouter::RouteProcess('void', new VoidOrderProcess());

// create singleton with secret key
$handler = NotificationRouter::GetNotificationHandler('1234');




// Create and configure Slim app
$app = new \Slim\App;

// Define app routes
$app->post('/notify', function (Request $request, Response $response) {

	// get body from request
    $parsedBody = $request->getParsedBody();

    // get notification handler
	$handler = NotificationRouter::GetNotificationHandler();

	try{
		// call handler with pass through and debug mode
		$h = $handler->handle($parsedBody, true, true);
		// respond 200 "ok"
		$response->getBody()->write("OK");

	} catch (\Exception $ex){
		// catch exceptions and log them to STDERR
		fputs(STDERR, "[Notification Error] - " . $ex->getMessage()."\n");
		// respond with error
		// $response = $response->withStatus(400);
		// $response->getBody()->write($ex->getMessage());
		// respond 200 "ok"
		$response->getBody()->write("OK");
	}

    return $response;
});

// Run app
$app->run();