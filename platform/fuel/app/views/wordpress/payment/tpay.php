<!DOCTYPE html>
<html lang="<?= $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title>TPAY.COM - <?= $full_token; ?></title>
</head>
<body>
    <p>
        <?= _("Redirecting to the payment page..."); ?>
    </p>
    <form action="https://secure.tpay.com" method="post" accept-charset="utf-8" id="tpayform">
        <input type="hidden" 
               name="id" 
               value="<?= Security::htmlentities($tpay_id); ?>">
        <input type="hidden" 
               name="kwota" 
               value="<?= Security::htmlentities($pln_amount); ?>">
        <input type="hidden" 
               name="opis" 
               value="<?= $description; ?>">
        <input type="hidden" 
               name="crc" 
               value="<?= $full_token; ?>">
        <input type="hidden" 
               name="wyn_url" 
               value="<?= $urls['wyn']; ?>">
        <input type="hidden" 
               name="pow_url" 
               value="<?= $urls['pow']; ?>">
        <input type="hidden" 
               name="pow_url_blad" 
               value="<?= $urls['pow_blad']; ?>">
        <input type="hidden" 
               name="email" 
               value="<?= Security::htmlentities($user_email); ?>">
        
        <?php
            if (!empty($user_name)):
        ?>
                <input type="hidden" 
                       name="nazwisko" 
                       value="<?= Security::htmlentities($user_name); ?>">
        <?php
            endif;
            
            if (!empty($user_address)):
        ?>
                <input type="hidden" 
                       name="adres" 
                       value="<?= Security::htmlentities($user_address); ?>">
        <?php
            endif;
            
            if (!empty($user_city)):
        ?>
                <input type="hidden" 
                       name="miasto" 
                       value="<?= Security::htmlentities($user_city); ?>">
        <?php
            endif;
            
            if (!empty($user_zip)):
        ?>
                <input type="hidden" 
                       name="kod" 
                       value="<?= Security::htmlentities($user_zip); ?>">
        <?php
            endif;
            
            if (!empty($user_country)):
        ?>
                <input type="hidden" 
                       name="kraj" 
                       value="<?= Security::htmlentities($user_country); ?>">
        <?php
            endif;
            
            if (!empty($user_phone)):
        ?>
                <input type="hidden" 
                       name="telefon" 
                       value="<?= Security::htmlentities($user_phone); ?>">
        <?php
            endif;
        ?>
        <input type="hidden" name="jezyk" value="<?= $lang; ?>">
        <input type="hidden" name="md5sum" value="<?= $md5_hidden; ?>">
    </form>
    
    <script type="text/javascript">
        document.getElementById("tpayform").submit();
    </script>
</body>
</html>