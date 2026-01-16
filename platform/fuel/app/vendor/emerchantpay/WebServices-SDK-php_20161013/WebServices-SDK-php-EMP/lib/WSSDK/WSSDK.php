<?php
/**
  * STUFF
  * @author Matthew Lothian <matthew.lothian@industrieit.com>
  */

require_once __DIR__."/Xml.php";
require_once __DIR__."/Model/BaseModel.php";
require_once __DIR__."/Model/Credentials.php";
require_once __DIR__."/Model/Currency.php";
require_once __DIR__."/Request/BaseRequest.php";
require_once __DIR__."/Response/BaseResponse.php";

require_once __DIR__."/Model/Customer/CustomerBase.php";
require_once __DIR__."/Model/PaymentTypes/PaymentType.php";
require_once __DIR__."/Model/Item/Item.php";
require_once __DIR__."/Model/RiskProvider/RiskProvider.php";
require_once __DIR__."/Model/AirlineData/AirlineData.php";

require_once __DIR__."/Request/InPayGetBanksRequest.php";
require_once __DIR__."/Request/InPayGetInstructionsRequest.php";
require_once __DIR__."/Request/iDEALABNGetBanksRequest.php";
require_once __DIR__."/Request/EarthportGetPayoutRequiredDataRequest.php";
require_once __DIR__."/Request/OrderSubmitRequest.php";
require_once __DIR__."/Request/RetrieveOrderRequest.php";
require_once __DIR__."/Request/RetrieveTransactionRequest.php";
require_once __DIR__."/Request/RetrieveFraudDataRequest.php";
require_once __DIR__."/Request/MerchantManagedRebillRequest.php";
require_once __DIR__."/Request/OrderSettleRequest.php";
require_once __DIR__."/Request/OrderCreditRequest.php";
require_once __DIR__."/Request/OrderCFTRequest.php";
require_once __DIR__."/Request/OrderDirectCFTRequest.php";
require_once __DIR__."/Request/OrderVoidRequest.php";
require_once __DIR__."/Request/OrderPayoutRequest.php";
require_once __DIR__."/Request/OrderRebillInstantUpgradeRequest.php";
require_once __DIR__."/Request/OrderCancelRebillRequest.php";
require_once __DIR__."/Request/BlackWhiteListRequest.php";
require_once __DIR__."/Request/CustomerRequest.php";
require_once __DIR__."/Request/RetrieveAirlineDataRequest.php";
require_once __DIR__."/Request/AuthenticateVBV3DRequest.php";
require_once __DIR__."/Request/RetrieveVBV3DRequest.php";
require_once __DIR__."/Request/PhoneVerifyRequest.php";
require_once __DIR__."/Request/RetrieveChargebackRequest.php";


use \WSSDK\Model as Model;
use \WSSDK\Request as Request;

/**
 * The Main entry point for the API library. 
 * Provides a wrapper for all the available 
 * requests and the merchant's credentials
 *
 * @example examples/init.php using the WSSDK class
 * 
 * @version WS SDK (PHP) 1.1.2
 */
class WSSDK {

	/**
	 * Stores Account Manages credentials
	 * @var Model\Credentials
	 */
	private $credentials;
	/**
	 * Host domain to hosted WSSDK API
	 * @var String
	 */
	private $APIDomain;
	/**
	 * The SSL Version to use when making requests:
	 * 0 CURL_SSLVERSION_DEFAULT: The default action. This will attempt to figure out the remote SSL protocol version.
     * 1 CURL_SSLVERSION_TLSv1: TLSv1.x
     * 2 CURL_SSLVERSION_SSLv2: SSLv2
     * 3 CURL_SSLVERSION_SSLv3: SSLv3
     * 4 CURL_SSLVERSION_TLSv1_0: TLSv1.0 (Added in 7.34.0)
     * 5 CURL_SSLVERSION_TLSv1_1: TLSv1.1 (Added in 7.34.0)
     * 6 CURL_SSLVERSION_TLSv1_2: TLSv1.2 (Added in 7.34.0)
	 * @var Number
	 */
	private $SSLVersion;

	/**
	 * WS SDK (PHP) 1.1.2
	 */
	static $VERSION; 

	/**
	 * set API constants
	 * @param String $ManagerID The Account ID of the eCommerce ManagerAccount.
	 * @param String $APIToken The API Key provided by your Account Manager
	 * @param String $APIDomain Host domain to hosted WSSDK API
	 * @param CURL_SSLVERSION the SSL version to use when making requests
	 */
	public function __construct($ManagerID, $APIToken, $APIDomain, $SSLVersion = 0)
	{
		WSSDK::$VERSION = "WS SDK IPGPAY (PHP) 1.1.2 (PHP " . PHP_VERSION . "; " . PHP_OS . ")";

		$this->credentials = new Model\Credentials($ManagerID, $APIToken);
		$this->APIDomain = 'https://' . rtrim($APIDomain, '/');
		$this->SSLVersion = $SSLVersion;

		$this->baseHeaders = (object) [
			'User-Agent' => WSSDK::$VERSION,
			'Accept' => 'application/xml',
			'charset' => 'utf-8'
        ];
	}

	/**
	 * expose domain in use
	 * @return String
	 */
	public function getEndpoint(){
		return $this->APIDomain;
	}

	/**
	 * This service allows direct payments via the Web Services API. It supports one-off and Gateway Managed Rebilling.
	 * @param  Model\OrderSubmit $model
	 * @param  boolean           $isTest
	 * @uses  Request\OrderSubmitRequest
	 * @uses  Model\OrderSubmit
	 * @return \WSSDK\BaseRequest
	 */
	public function orderSubmitRequest(Model\OrderSubmit $model, $isTest = false){

		$req = new Request\OrderSubmitRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
/**
 * This service allows retrieval of Order data for reconciliation.
 * @param  Model\RetrieveOrder $model
 * @param  boolean             $isTest
 * @return \WSSDK\BaseRequest
 */
	public function retrieveOrderRequest(Model\RetrieveOrder $model, $isTest = false){

		$req = new Request\RetrieveOrderRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows retrieval of transaction data for reconciliation.
	 * @param  Model\RetrieveTransaction $model  
	 * @param  boolean                   $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function retrieveTransactionRequest(Model\RetrieveTransaction $model, $isTest = false){

		$req = new Request\RetrieveTransactionRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows retrieval of all TC40/SAFE data related to your accounts. This data is provided by
	 * selected payment acquirers, which in turn receive this data from the card associations VISA and
	 * Mastercard.
	 * @param  Model\RetrieveFraudData $model  
	 * @param  boolean                 $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function retrieveFraudDataRequest(Model\RetrieveFraudData $model, $isTest = false){

		$req = new Request\RetrieveFraudDataRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * Merchant Managed Rebilling allows Merchant to control the Rebill schedule, or to generate adhoc
	 * payments referencing an existing Order.
	 * @param  Model\MerchantManagedRebill $model  
	 * @param  boolean                     $isTest 
	 * @return \WSSDK\BaseRequest  
	 */
	public function merchantManagedRebillRequest(Model\MerchantManagedRebill $model, $isTest = false){

		$req = new Request\MerchantManagedRebillRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This method will settle an Order previously performed as an Auth. This will generate an email
	 * notification to the Customer on success when Customer emails have been configured in your
	 * Merchant Configuration. Notifications to the Merchant are enabled by default.
	 * @param  Model\OrderSettle $model  
	 * @param  boolean           $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function orderSettleRequest(Model\OrderSettle $model, $isTest = false){

		$req = new Request\OrderSettleRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This method will credit the specified Item/Transaction. Multiple items can be credited in the one
	 * request. For Auth transactions – you must provide the Transaction ID of the Settlement transaction. If
	 * you wish to reverse the Auth you should use the Void method instead.
	 * @param  Model\OrderCredit $model  
	 * @param  boolean           $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function orderCreditRequest(Model\OrderCredit $model, $isTest = false){

		$req = new Request\OrderCreditRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This method will perform a cardholder funds transfer for the specified order.
	 * @param  Model\OrderCFT $model  
	 * @param  boolean        $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function orderCFTRequest(Model\OrderCFT $model, $isTest = false){

		$req = new Request\OrderCFTRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows the creation of CFT payouts to customers without a previous order - or payout
	 * using a Bank Account via Earthport to customers without a previous order.
	 * @param  Model\OrderDirectCFT $model  
	 * @param  boolean              $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function orderDirectCFTRequest(Model\OrderDirectCFT $model, $isTest = false){

		$req = new Request\OrderDirectCFTRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This method will void the specified Order. This method is only available if the original transaction was
	 * an Auth. Notification to the customer is not performed.
	 * @param  Model\OrderVoid $model  
	 * @param  boolean         $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function orderVoidRequest(Model\OrderVoid $model, $isTest = false){

		$req = new Request\OrderVoidRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows direct payouts to customers via the Web Services API.
	 * @param  Model\OrderPayout $model
	 * @param  boolean           $isTest
	 * @return \WSSDK\BaseRequest
	 */
	public function orderPayoutRequest(Model\OrderPayout $model, $isTest = false){

		$req = new Request\OrderPayoutRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This method will instantly upgrade an existing Rebill. Upgrade means to explicitly bring forward the
	 * Rebilling date immediately, perform a transaction and start the rebilling period from now. The initial
	 * trial period for the item will no longer be valid.
	 * 
	 * This is only available on items that have an initial trial period. Any password for this item will have its
	 * expiry date extended by the current rebilling period. The customer will be sent an email notification,
	 * however, no server notifications will be sent to the merchant.
	 * 
	 * If the original transaction was an Auth and has not yet been settled, it will not be automatically
	 * settled. The Merchant must settle this manually either through the Gateway or using the Settlement
	 * Web Service method. If there is no initial period, the request will be rejected. Instant upgrade is only
	 * available for credit card transactions. Alternate payments are not supported.
	 * @param  Model\OrderRebillInstantUpgrade $model  
	 * @param  boolean                         $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function orderRebillInstantUpgradeRequest(Model\OrderRebillInstantUpgrade $model, $isTest = false){

		$req = new Request\OrderRebillInstantUpgradeRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This method will cancel an existing Rebill. No Customer notification is performed. Reversal of a
	 * cancellation is not supported. Usernames and Passwords associated with this Rebill will expire when
	 * the current Rebill cycle elapses.
	 * @param  Model\OrderCancelRebill $model  
	 * @param  boolean                 $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function orderCancelRebillRequest(Model\OrderCancelRebill $model, $isTest = false){

		$req = new Request\OrderCancelRebillRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This method allows you to add entries to the Merchant Blacklist.
	 * @param Model\EntrieIdentity $model  
	 * @param boolean              $isTest 
	 */
	public function addToBlacklistRequest(Model\BlackListSearch $model, $isTest = false){

		$req = new Request\AddToBlackListRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This method allows you to remove entries from the Merchant Blacklist.
	 * @param  Model\EntrieIdentity $model  
	 * @param  boolean              $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function removeFromBlacklistRequest(Model\BlackListSearch $model, $isTest = false){

		$req = new Request\removeFromBlacklistRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This method allows you to add entries to the Merchant Whitelist.
	 * @param Model\EntrieIdentity $model  
	 * @param boolean              $isTest 
	 */
	public function addToWhitelistRequest(Model\WhiteListSearch $model, $isTest = false){

		$req = new Request\AddToWhitelistRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This method allows you to remove entries from the Merchant Whitelist.
	 * @param  Model\EntrieIdentity $model  
	 * @param  boolean              $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function removeFromWhitelistRequest(Model\WhiteListSearch $model, $isTest = false){

		$req = new Request\RemoveFromWhitelistRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows retrieval of previously created customers for reconciliation purposes.
	 * @param  Model\CustomerRetrieve $model  
	 * @param  boolean                $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function customerRetrieveRequest(Model\CustomerRetrieve $model, $isTest = false){

		$req = Request\CustomerRequest::Retrieve(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * The Customer Entity allows an easy way to group orders together for a single customer. Once a
	 * Customer ID is created - you can pass this information through on creation of new orders.
	 * @param  Model\CustomerCreate $model  
	 * @param  boolean              $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function customerCreateRequest(Model\CustomerCreate $model, $isTest = false){

		$req = Request\CustomerRequest::Create(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * The Customer Entity allows an easy way to group orders together for a single customer. This function
	 * allows you to update an existing customer with a new name or email address.
	 * @param  Model\CustomerUpdate $model  
	 * @param  boolean              $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function customerUpdateRequest(Model\CustomerUpdate $model, $isTest = false){

		$req = Request\CustomerRequest::Update(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This method allows you to retrieve previous card details used by the customer. Only cards which have
	 * been flagged by the customer and merchant to remember will be retrieved. Expired cards will not be
	 * retrieved. This services does not support non-credit card payment methods.
	 * @param  Model\CustomerGetCards $model  
	 * @param  boolean                $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function customerGetCardsRequest(Model\CustomerGetCards $model, $isTest = false){

		$req = Request\CustomerRequest::GetCards(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows retrieval of airline data for transactions which included airline data in the transaction request.
	 * 
	 * This data is only available from supported payment acquirers. Please contact your Account Manager for more details.
	 * @param  Model\RetrieveAirlineData $model  
	 * @param  boolean                   $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function retrieveAirlineDataRequest(Model\RetrieveAirlineData $model, $isTest = false){

		$req = new Request\RetrieveAirlineDataRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows initiation of a VBV/3D Secure Authentication request.
	 * @param  Model\AuthenticateVBV3D $model  
	 * @param  boolean                 $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function authenticateVBV3DRequest(Model\AuthenticateVBV3D $model, $isTest = false){

		$req = new Request\AuthenticateVBV3DRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows retrieval of the results for a VBV/3D Secure Authentication request. This allows the merchant to retrieve fields required for submission in the Order Submit API method.
	 * @param  Model\RetrieveVBV3D $model  
	 * @param  boolean             $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function retrieveVBV3DRequest(Model\RetrieveVBV3D $model, $isTest = false){

		$req = new Request\RetrieveVBV3DRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows retrieval of chargeback data for reconciliation purposes.
	 * @param  Model\RetrieveChargeback $model  
	 * @param  boolean                  $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function retrieveChargebackRequest(Model\RetrieveChargeback $model, $isTest = false){

		$req = new Request\RetrieveChargebackRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * Phone verify provides the ability to validate the identity of a customer using a phone verification 
	 * service. It works by sending a unique code to the customer phone number that is then entered by the
	 * customer on the payment form and submitted with your payment request.
	 * @param  Model\PhoneVerify $model  
	 * @param  boolean           $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function phoneVerifyRequest(Model\PhoneVerify $model, $isTest = false){

		$req = new Request\PhoneVerifyRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows retrieval of a list of INPay supported banks based on the customers country. The
	 * resulting bank id can then be passed into the Order Submit web service when making an INPay
	 * payment.This service is only available for merchants who have INPay accounts. Refer to Appendix API Documentation:
	 * INPay For more information.
	 * @param  Model\PaymentType\InPayGetBanks $model  
	 * @param  boolean                         $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function inPayGetBanksRequest(Model\PaymentType\InPayGetBanks $model, $isTest = false){

		$req = new Request\InPayGetBanksRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows retrieval of the payment instructions for an existing Order. The payment
	 * instructions provide the customer with the information they need to make the manual bank payment.
	 * This information is included in the initial response to the Order Submit API method for the INPay
	 * payment method. This API is intended for use where the payment instructions need to be retrieved again by the Merchant.
	 * 
	 * This service is only available for merchants who have INPay accounts. Refer to Appendix: INPay For more information.
	 * @param  Model\PaymentType\InPayGetInstructions $model  
	 * @param  boolean    $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function inPayGetInstructionsRequest(Model\PaymentType\InPayGetInstructions $model, $isTest = false){

		$req = new Request\InPayGetInstructionsRequest(
			$model, 
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}
	/**
	 * This service allows retrieval of a list of iDEAL ABN supported banks. The resulting bank id can then be
	 * passed into the Order Submit service when making an iDEAL ABN payment.This service is only
	 * available for merchants who have iDEAL ABN accounts. Refer to Appendix: iDEAL ABN For more information.
	 * 
	 * It is recommended that this service be called once per day, and the resulting list cached in the merchants application/system.
	 * @param  boolean $isTest 
	 * @return \WSSDK\BaseRequest
	 */
	public function iDEALABNGetBanksRequest($isTest = false){

		$req = new Request\iDEALABNGetBanksRequest(
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}

	/**
	 * This method will return all the required beneficiary bank account parameters for a EarthPort payout
	 * transaction. The merchant must then present these fields to the customer to enter the required
	 * information.
	 * 
	 * This information must be provided to the Payout without Original Order method, when performing a
	 * payout.
	 * @param Model\PaymentType\EarthportGetPayoutRequiredData $model
	 * @param boolean                                          $isTest
	 * @return \WSSDK\BaseRequest
	 */
	public function EarthportGetPayoutRequiredDataRequest(Model\PaymentType\EarthportGetPayoutRequiredData $model, $isTest = false){

		$req = new Request\iDEALABNGetBanksRequest(
			$this->credentials, 
			$this->APIDomain,
			$this->baseHeaders,
			$this->SSLVersion,
			$isTest
		);
		return $req;

	}

}

