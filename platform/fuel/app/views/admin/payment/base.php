<?php

use Modules\Payments\PaymentRegistry;

include(APPPATH . "views/admin/payment/skrill.php");
include(APPPATH . "views/admin/payment/neteller.php");
include(APPPATH . "views/admin/payment/tpay.php");
include(APPPATH . "views/admin/payment/cubits.php");
include(APPPATH . "views/admin/payment/sofort.php");
include(APPPATH . "views/admin/payment/entercash.php");
include(APPPATH . "views/admin/payment/piastrix.php");
include(APPPATH . "views/admin/payment/ecopayz.php");
include(APPPATH . "views/admin/payment/paysafecard.php");
include(APPPATH . "views/admin/payment/entropay.php");
include(APPPATH . "views/admin/payment/coinpayments.php");
include(APPPATH . "views/admin/payment/asiapayment.php");
include(APPPATH . "views/admin/payment/paypal.php");
include(APPPATH . "views/admin/payment/bitbaypay.php");
include(APPPATH . "views/admin/payment/dusupay.php");
include(APPPATH . "views/admin/payment/easypaymentgateway.php");
include(APPPATH . "views/admin/payment/apcopaycc.php");
include(APPPATH . "views/admin/payment/astropay.php");
include(APPPATH . "views/admin/payment/stripe.php");
include(APPPATH . "views/admin/payment/flutterwave.php");
include(APPPATH . "views/admin/payment/flutterwave_africa.php");
include(APPPATH . "views/admin/payment/astropaycard.php");
include(APPPATH . "views/admin/payment/creditcardsandbox.php");
include(APPPATH . "views/admin/payment/truevocc.php");
include(APPPATH . "views/admin/payment/visanet.php");
include(APPPATH . "views/admin/payment/custom.php");
include(APPPATH . "views/admin/payment/bhartipay.php");
include(APPPATH . "views/admin/payment/sepa.php");
include(APPPATH . "views/admin/payment/payop.php");
include(APPPATH . "views/admin/payment/wonderlandpay.php");
include(APPPATH . "views/admin/payment/picksell.php");
include(APPPATH . "views/admin/payment/pspgate.php");
include(APPPATH . 'views/admin/payment/zen.php');
include(APPPATH . 'views/admin/payment/onramper.php');
include(APPPATH . 'views/admin/payment/nowpayments.php');
include(APPPATH . 'views/admin/payment/gcash.php');
include(APPPATH . 'views/admin/payment/lenco.php');

/** @var PaymentRegistry $registry */
$registry = Container::get(PaymentRegistry::class);
$registry->registerViews();
