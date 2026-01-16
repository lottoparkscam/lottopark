<!DOCTYPE html>
<html lang="EN">
<head>
    <meta charset="UTF-8">
    <title>Trustpayments</title>
</head>
<body>
<p>
    <?= _("Redirecting to the payment page..."); ?>
    <?php

# @see https://docs.trustpayments.com/document/knowledge-base/authorisations/ppg-fields/?>
</p>
<form action="<?= $targetUrl ?>" method="POST" accept-charset="utf-8" id="trustpaymentsForm" name="trustpaymentsForm">
    <input type="hidden" name="sitereference" value="<?= $sitereference ?>">
    <input type="hidden" name="stprofile" value="default">
    <input type="hidden" name="stdefaultprofile" value="st_paymentcardonly">
    <input type="hidden" name="currencyiso3a" value="<?= $currencyiso3a ?>">
    <input type="hidden" name="mainamount" value="<?= $mainamount ?>">
    <input type="hidden" name="version" value="2">

    <input type="hidden" name="paymenttypedescription" value="DELTA">

    <input type=hidden name="ruleidentifier" value="STR-8">
    <input type="hidden" name="successfulurlnotification" value="<?= $ipnConfirmUrl ?>">
    <input type="hidden" name="stextraurlnotifyfields" value="transactionstartedtimestamp">
    <input type="hidden" name="stextraurlnotifyfields" value="sitereference">
    <input type="hidden" name="stextraurlnotifyfields" value="interface">
    <input type="hidden" name="stextraurlnotifyfields" value="livestatus">
    <input type="hidden" name="stextraurlnotifyfields" value="issuer">
    <input type="hidden" name="stextraurlnotifyfields" value="settleduedate">
    <input type="hidden" name="stextraurlnotifyfields" value="errorcode">
    <input type="hidden" name="stextraurlnotifyfields" value="baseamount">
    <input type="hidden" name="stextraurlnotifyfields" value="tid">
    <input type="hidden" name="stextraurlnotifyfields" value="securityresponsepostcode">
    <input type="hidden" name="stextraurlnotifyfields" value="transactionreference">
    <input type="hidden" name="stextraurlnotifyfields" value="merchantname">
    <input type="hidden" name="stextraurlnotifyfields" value="paymenttypedescription">
    <input type="hidden" name="stextraurlnotifyfields" value="accounttypedescription">
    <input type="hidden" name="stextraurlnotifyfields" value="fraudrating">
    <input type="hidden" name="stextraurlnotifyfields" value="splitfinalnumber">
    <input type="hidden" name="stextraurlnotifyfields" value="acquirerresponsecode">
    <input type="hidden" name="stextraurlnotifyfields" value="requesttypedescription">
    <input type="hidden" name="stextraurlnotifyfields" value="expirydate">
    <input type="hidden" name="stextraurlnotifyfields" value="securityresponsesecuritycode">
    <input type="hidden" name="stextraurlnotifyfields" value="currencyiso3a">
    <input type="hidden" name="stextraurlnotifyfields" value="authcode">
    <input type="hidden" name="stextraurlnotifyfields" value="settlebaseamount">
    <input type="hidden" name="stextraurlnotifyfields" value="errormessage">
    <input type="hidden" name="stextraurlnotifyfields" value="merchantcountryiso2a">
    <input type="hidden" name="stextraurlnotifyfields" value="maskedpan">
    <input type="hidden" name="stextraurlnotifyfields" value="securityresponseaddress">
    <input type="hidden" name="stextraurlnotifyfields" value="operatorname">
    <input type="hidden" name="stextraurlnotifyfields" value="settlestatus">

    <input type=hidden name="ruleidentifier" value="STR-6">
    <input type="hidden" name="successfulurlredirect" value="<?= $returnUrl ?>">

    <input type="hidden" name="billingfirstname" value="<?= $user['name'] ?>">
    <input type="hidden" name="billingpremise" value="<?= $user['address_2'] ?>">
    <input type="hidden" name="billingstreet" value="<?= $user['address_1'] ?>">
    <input type="hidden" name="billingtown" value="<?= $user['city'] ?>">
    <input type="hidden" name="billingcounty" value="<?= $user['country'] ?>">
    <input type="hidden" name="billingpostcode" value="<?= $user['zip'] ?>">
    <input type="hidden" name="billingemail " value="<?= $user['email'] ?>">
    <input type="hidden" name="billingtelephone " value="<?= $user['phone'] ?>">

    <input type="hidden" name="locale" value="<?= $locale ?>">
</form>

<script type="text/javascript">
  const payForm = document.trustpaymentsForm;
  payForm.submit();
</script>
</body>
</html>
