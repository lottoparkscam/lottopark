<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>DusuPay - <?php echo $post_data['token']; ?></title>
    </head>
    <body>
        <p><?php echo _("Redirecting to the payment page..."); ?></p>
        <form method="POST" action="<?php echo $post_data['apiurl']; ?>" id="dusupaygateway" target="_self">
            <input type="hidden" name="dusupay_merchantId" 
                   value="<?php echo Security::htmlentities($post_data['dusupay_merchantId']); ?>">
            <input type="hidden" name="dusupay_amount" 
                   value="<?php echo Security::htmlentities($post_data['dusupay_amount']); ?>">
            <input type="hidden" name="dusupay_currency" 
                   value="<?php echo Security::htmlentities($post_data['dusupay_currency']); ?>">
            <input type="hidden" name="dusupay_itemId" 
                   value="<?php echo Security::htmlentities($post_data['dusupay_itemId']); ?>">
            <input type="hidden" name="dusupay_itemName" 
                   value="<?php echo Security::htmlentities($post_data['dusupay_itemName']); ?>">
            <input type="hidden" name="dusupay_transactionReference" 
                   value="<?php echo Security::htmlentities($post_data['dusupay_transactionReference']); ?>">
            <input type="hidden" name="dusupay_redirectURL" 
                   value="<?php echo Security::htmlentities($post_data['dusupay_redirectURL']); ?>">
            <input type="hidden" name="dusupay_successURL" 
                   value="<?php echo Security::htmlentities($post_data['dusupay_successURL']); ?>" >
        </form>
        <script>
            document.getElementById("dusupaygateway").submit();
        </script>
    </body>
</html>


