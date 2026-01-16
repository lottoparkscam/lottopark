<?php

use Fuel\Core\Security;

?>
<!DOCTYPE html>
<html lang="EN">
<head>
    <meta charset="UTF-8">
    <title>Tamspay</title>
</head>
<body>
<p>
    <?= _("Redirecting to the payment page..."); ?>
</p>
<form action="<?= $targetUrl ?>" method="POST" accept-charset="utf-8" id="tamspayForm" name="tamspayForm">
    <input type="hidden" name="sid" value="<?= Security::htmlentities($sid); ?>"/>
    <input type="hidden" name="amount" value="<?= Security::htmlentities($amount); ?>"/>
    <input type="hidden" name="userId" value="<?= Security::htmlentities($userId); ?>"/>
    <input type="hidden" name="phoneNum" value="<?= Security::htmlentities($phoneNum); ?>"/>
    <input type="hidden" name="orderId" value="<?= Security::htmlentities($orderId); ?>"/>
    <input type="hidden" name="productName" value="<?= Security::htmlentities($productName); ?>"/>
    <input id="returnUrl" type="hidden" name="returnUrl" value="<?= $returnUrl; ?>"/> <?php # POST IPN notification?>
    <input id="userUrl" type="hidden" name="userUrl" value="<?= $userUrl; ?>"/><?php # redirection url after popup finalization?>
</form>

<script type="text/javascript">
  const payForm = document.tamspayForm;
  payForm.submit();
</script>
</body>
</html>
