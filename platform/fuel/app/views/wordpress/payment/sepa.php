<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Sepa - <?= $token; ?></title>
    </head>
    <body>
        <form method="POST" action="<?= $action_url; ?>" id="sepaform">
            <input type="hidden" name="memberId" value="<?= $member_id ;?>" />
            <input type="hidden" name="totype" value="<?= $to_type ;?>" />
            <input type="hidden" name="amount" value="<?= $amount; ?>" />
            <input type="hidden" name="merchantTransactionId" value="<?= $merchant_transaction_id; ?>" />
            <input type="hidden" name="checksum" value="<?= $checksum; ?>" />
            <input type="hidden" name="merchantRedirectUrl" value="<?= $merchant_redirect_url; ?>" />
            <input type="hidden" name="notificationUrl" value="<?= $notification_url; ?>" />
            <input type="hidden" name="currency" value="<?= $currency; ?>" />
            
            <?php
                if (!empty($country)):
            ?>
                    <input type="hidden" name="country" value="<?= $country; ?>" />
            <?php
                endif;
                
                if (!empty($city)):
            ?>
                    <input type="hidden" name="city" value="<?= $city; ?>" />
            <?php
                endif;
                
                if (!empty($state)):
            ?>
                    <input type="hidden" name="state" value="<?= $state; ?>" />
            <?php
                endif;
                
                if (!empty($postcode)):
            ?>
                    <input type="hidden" name="postcode" value="<?= $postcode; ?>" />
            <?php
                endif;
                
                if (!empty($street)):
            ?>
                    <input type="hidden" name="street" value="<?= $street; ?>" />
            <?php
                endif;
                
                if (!empty($telnocc) && !empty($phone)):
            ?>
                    <input type="hidden" name="telnocc" value="<?= $telnocc; ?>" />
                    <input type="hidden" name="phone" value="<?= $phone; ?>" />
            <?php
                endif;
                
                if (!empty($email)):
            ?>
                    <input type="hidden" name="email" value="<?= $email; ?>" />
            <?php
                endif;
                
                if (!empty($ip)):
            ?>
                    <input type="hidden" name="ip" value="<?= $ip; ?>" />
            <?php
                endif;
            ?>
        </form>
        <script type="text/javascript">
            document.getElementById("sepaform").submit();
        </script>
    </body>
</html>


