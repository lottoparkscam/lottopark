<?php
    $token = Security::htmlentities($post_data['token']);
    
    $publishable_key = Security::htmlentities($post_data['publishable_key']);
    $session_id = Security::htmlentities($post_data['stripe_session_id']);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Stripe - <?= $token; ?></title>
    </head>
    <body>
        <p>
            <?= _("Redirecting to the payment page..."); ?>
        </p>
        
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            var stripe = Stripe('<?= $publishable_key; ?>');
                
            stripe.redirectToCheckout({
                sessionId: '<?= $session_id; ?>'
            }).then(function (result) {
                console.log(result);
            });
        </script>
    </body>
</html>


