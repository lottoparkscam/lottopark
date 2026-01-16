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
<html>
<head>
    <meta charset="UTF-8">
    <title>COINPAYMENTS.NET - <?php echo $full_token; ?></title>
</head>
<body>
    <p>
        <?php echo _("Redirecting to the payment page..."); ?>
    </p>
    <form action="https://www.coinpayments.net/index.php" 
          method="post" 
          accept-charset="utf-8" 
          id="coinpaymentsform">
        <?php
            foreach ($request as $key => $item):
        ?>
                <input type="hidden" 
                       name="<?php echo $key; ?>" 
                       value="<?php echo Security::htmlentities($item); ?>" />
        <?php
            endforeach;
        ?>
    </form>
    
    <script type="text/javascript">
        document.getElementById("coinpaymentsform").submit();
    </script>
</body>
</html>