<?php
/**
  * @filesource
  */

namespace WSSDK\Request;
use \WSSDK\Model as Model;
use \WSSDK\BaseRequest as BaseRequest; 
{

	require_once __DIR__."/../Model/OrderPayout.php";
	require_once __DIR__."/../Model/OrderPayoutPaymentType.php";
	require_once __DIR__."/../Model/OrderPayoutWebMoney.php";
	require_once __DIR__."/../Model/OrderPayoutNeteller.php";
	require_once __DIR__."/../Model/OrderPayoutUKash.php";
	require_once __DIR__."/../Model/OrderPayoutINPay.php";
	require_once __DIR__."/../Model/OrderPayoutMoneta.php";

	/**
	 * 
	 */
	class OrderPayoutRequest extends BaseRequest {

		public function __construct(Model\OrderPayout $model, Model\Credentials $credentials, $APIDomain = null, $headers = null, $SSLVersion = 0, $test = false){
			parent::__construct($credentials, "$APIDomain/service/order/payout", $headers, $SSLVersion, $test);
			$this->body['model'] = $model;
		}

		protected function buildResponse ($xmlString, $headers){
			return new StatusResponse($xmlString, $headers);
		}

		public function setPaymentType(PaymentType\OrderPayoutPaymentType $model){
			$this->body['PaymentType'] = $model;
			$this->body['model']->setPaymentType($model->getPaymentTypeKey());
		}

	};

};

