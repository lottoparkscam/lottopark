<?php
    $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
    
    if (!empty($whitelabel) &&
        Helpers_Whitelabel::is_V1((int)$whitelabel['type'])
    ):
?>
        <p class="payment-info">
            <?= _("Your credit card will be charged by \"{$whitelabel['name']} (+44 20 3514 2397)\".") ?>
        </p>
<?php
    endif;
