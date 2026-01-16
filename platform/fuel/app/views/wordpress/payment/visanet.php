<?php
    $token = Security::htmlentities($post_data['token']);
    
    $return_url = Security::htmlentities($post_data['return_url']);
    
    $source_url = Security::htmlentities($post_data['source_url']);
    
    $session_token = Security::htmlentities($post_data['session_token']);
    
    $merchant_id = Security::htmlentities($post_data['merchant_id']);
    
    $channel = Security::htmlentities($post_data['channel']);
    
    //$marchant_logo = Security::htmlentities($post_data['channel']);
    
    $marachant_name = Security::htmlentities($post_data['marchant_name']);
    
    $amount = Security::htmlentities($post_data['amount']);
    
    $purchase_number = Security::htmlentities($post_data['purchase_number']);
    
    //$card_holder_name = Security::htmlentities($post_data['card_holder_name']);
    
    //$card_holder_last_name = Security::htmlentities($post_data['card_holder_last_name']);
    
    $expiration_minutes = Security::htmlentities($post_data['expiration_minutes']);
    
    $timeout_url = Security::htmlentities($post_data['timeout_url']);
    
    $user_token = Security::htmlentities($post_data['user_token']);
    
    $button_background_url = "background: url('https://static-content-qas.vnforapps.com/v2/img/button/ES/navy/default/PayWith.png');";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>VisaNet - <?= $token; ?></title>
    </head>
    <body>
        
        <p>
            <?= _("Please click on the button below to complete your payment."); ?>
        </p>
        
        <form action="<?= $return_url; ?>" method="POST">
            <script src="<?= $source_url; ?>" 
                    data-sessiontoken="<?= $session_token; ?>" 
                    data-merchantid="<?= $merchant_id; ?>" 
                    data-channel="<?= $channel; ?>" 
                    data-buttonsize
                    data-buttoncolor
                    data-merchantlogo 
                    data-merchantname="<?= $marachant_name; ?>" 
                    data-formbuttoncolor="#0A0A2A" 
                    data-showamount="TRUE" 
                    data-amount="<?= $amount; ?>" 
                    data-purchasenumber="<?= $purchase_number; ?>" 
                    data-cardholdername 
                    data-cardholderlastname 
                    data-cardholderemail 
                    data-expirationminutes="<?= $expiration_minutes; ?>" 
                    data-timeouturl="<?= $timeout_url; ?>" 
                    data-usertoken="<?= $user_token; ?>" 
                    data-recurrence 
                    data-frequency 
                    data-recurrencetype 
                    data-recurrenceamount 
                    data-documenttype="0" 
                    data-documentid 
                    data-beneficiaryid="<?= $token; ?>" 
                    data-productid 
                    data-phone ></script>
        </form>
        
    </body>
</html>


