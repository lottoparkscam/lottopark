<?php
/**
  * @filesource
  */

namespace WSSDK\Request;

require_once __DIR__."/../Model/Shipping.php";
require_once __DIR__."/../Model/DynamicDescriptor.php";
require_once __DIR__."/../Model/OrderSubmit.php";

//use \WSSDK\Credentials as Credentials;
use \WSSDK\BaseRequest as BaseRequest;
use \WSSDK\RequestValidationException as RequestValidationException;
use \WSSDK\BaseResponse as BaseResponse;
use \WSSDK\Currency as Currency;
use \WSSDK\Model as Model;
use \WSSDK\Model\PaymentType as PaymentType;
use \WSSDK\Model\Item as Item;


/**
 * @example examples/submitOrder.php using the WSSDK class
 */
class OrderSubmitRequest extends BaseRequest {

	private $itemCount;

	public function __construct(Model\OrderSubmit $Order, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
		parent::__construct($credentials, "$APIDomain/service/order/submit", $headers, $SSLVersion, $test);
		$this->body['Order'] = $Order;
		$this->itemCount = 1;
	}

	// public function setOrder(Order $model){
	// 	$this->body['order'] = $model;
	// }

	public function setShipping(Model\Shipping $model){
		$this->body['Shipping'] = $model;
	}

	public function setCustomer(Model\Customer $model){
		if (!is_null($this->body['Order']->getCustomerId())){
			throw new RequestValidationException(get_class($this) . ": Customer id found in request, customer model not allowed", 0); 
		}
		$this->body['Customer'] = $model;
	}

	public function setDynamicDescriptor(Model\DynamicDescriptor $model){
		$this->body['DynamicDescriptor'] = $model;
	}

	public function setAirlineData(Model\AirlineData $model){
		$this->body['AirlineData'] = $model;
	}

	public function setRiskProvider(Model\RiskProvider $model){
		$this->body[get_class($model)] = $model;
	}

	public function setPaymentType(PaymentType\PaymentType $model){
		$this->body['PaymentType'] = $model;
		$this->body['Order']->setPaymentType($model->getPaymentTypeKey());
	}

	public function addItem(Item\Item $model){

		if($this->itemCount > 50){
			throw new RequestValidationException(get_class($this) . ": A maximum of 50 items can be added to the request", 0);
		}

		$model->setItemNumber($this->itemCount);
		$this->body['item_'.$this->itemCount] = $model;
		$this->itemCount += 1;
	}

	protected function buildResponse ($xmlString, $headers){
		return new OrderResponse($xmlString, $headers);
	}

	protected function validate(){

		$order = $this->body['Order'];

		parent::validate();

	}

};

/***************
* Order Response
***************/
class OrderResponse extends BaseResponse {};

