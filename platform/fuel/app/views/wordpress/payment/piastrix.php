<?php
    $full_token = $whitelabel['prefix'];
    if ($transaction['type'] == Helpers_General::TYPE_TRANSACTION_PURCHASE) {
        $full_token .= 'P';
    } else {
        $full_token .= 'D';
    }
    $full_token .= $transaction['token'];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title>PIASTRIX.COM - <?php echo $full_token; ?></title>
</head>
<body>
    <p><?php echo _("Redirecting to the payment page..."); ?></p>
    <form action="https://pay.piastrix.com/<?php echo $lang; ?>/pay" 
          method="post" 
          accept-charset="utf-8" 
          id="piastrixform">
        <input type="hidden" 
               name="amount" 
               value="<?php echo Security::htmlentities($request['shop_amount']); ?>" />
        <input type="hidden" 
               name="currency" 
               value="<?php echo $request['shop_currency']; ?>" />
        <input type="hidden" 
               name="shop_id" 
               value="<?php echo $request['shop_id']; ?>" />
        <input type="hidden" 
               name="sign" 
               value="<?php echo $request['sign']; ?>" />
        <input type="hidden" 
               name="shop_order_id" 
               value="<?php echo $request['shop_order_id']; ?>" />
        <input type="hidden" 
               name="description" 
               value="<?php echo $request['description']; ?>" />
    </form>
    
    <script type="text/javascript">
        document.getElementById("piastrixform").submit();
    </script>
</body>
</html>