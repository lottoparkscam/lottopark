<?php

namespace WSSDK\Model\PaymentType;
require_once __DIR__."/CreditCard.php";
require_once __DIR__."/CreditCardVBV3D.php";
require_once __DIR__."/CreditCardVBV3DOTT.php";
require_once __DIR__."/BankTransfer.php";
require_once __DIR__."/CashU.php";
require_once __DIR__."/eZeeWallet.php";
require_once __DIR__."/iDEALABN.php";
require_once __DIR__."/Infin.php";
require_once __DIR__."/INPay.php";
require_once __DIR__."/Moneta.php";
require_once __DIR__."/Mopay.php";
require_once __DIR__."/Neteller.php";
require_once __DIR__."/OneCard.php";
require_once __DIR__."/PayByVoucher.php";
require_once __DIR__."/Paysafecard.php";
require_once __DIR__."/PayPal.php";
require_once __DIR__."/POLi.php";
require_once __DIR__."/PPro.php";
require_once __DIR__."/Raberil.php";
require_once __DIR__."/Sofort.php";
require_once __DIR__."/UATP.php";
require_once __DIR__."/Webmoney.php";
require_once __DIR__."/Yandex.php";
require_once __DIR__."/MBGateway.php";

use \WSSDK\Model as Model;

abstract class PaymentType extends Model\BaseModel {

	protected $paymentTypeKey;
	public function getPaymentTypeKey(){
		return $this->paymentTypeKey;
	}

}

