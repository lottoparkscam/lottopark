<h3>
    <?= _("Sepa"); ?>
</h3>
<?php
    if (isset($adata['paymentId'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("paymentId")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['paymentId']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['status'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("status")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['paymentBrand'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("paymentBrand")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['paymentBrand']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['paymentMode'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("paymentMode")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['paymentMode']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['firstName'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("firstName")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['firstName']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['lastName'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("lastName")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['lastName']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['amount']) && isset($adata['currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Amount")); ?>:</span>
        <span class="details-value"><?= Lotto_View::format_currency($adata['amount'], $adata['currency'], true); ?></span><br>
<?php
    endif;

    if (isset($adata['descriptor'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("descriptor")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['descriptor']); ?></span><br>
<?php
    endif;

    if (isset($adata['timestamp'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("timestamp")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['timestamp']); ?></span><br>
<?php
    endif;

    if (isset($adata['merchantTransactionId'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("merchantTransactionId")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['merchantTransactionId']); ?></span><br>
<?php
    endif;

    if (isset($adata['remark'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("remark")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['remark']); ?></span><br>
<?php
    endif;

    if (isset($adata['transactionStatus'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("transactionStatus")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transactionStatus']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['tmpl_amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("tmpl_amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['tmpl_amount']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['tmpl_currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("tmpl_currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['tmpl_currency']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['eci'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("eci")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['eci']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['checksum'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("checksum")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['checksum']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['result'])):
?>
        <h4>
            <?= _("result"); ?>
        </h4>
<?php
        if (isset($adata['result']['code'])):
?>
            <span class="details-label"><?= Security::htmlentities(_("code")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['result']['code']); ?></span><br>
<?php
        endif;

        if (isset($adata['result']['description'])):
?>
            <span class="details-label"><?= Security::htmlentities(_("description")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['result']['description']); ?></span><br>
<?php
        endif;
    endif;
    
    if (isset($adata['card'])):
?>
        <h4>
            <?= _("card"); ?>
        </h4>
<?php
        if (isset($adata['card']['bin'])):
?>
            <span class="details-label"><?= Security::htmlentities(_("bin")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['card']['bin']); ?></span><br>
<?php
        endif;

        if (isset($adata['card']['lastFourDigits'])):
?>
            <span class="details-label"><?= Security::htmlentities(_("lastFourDigits")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['card']['lastFourDigits']); ?></span><br>
<?php
        endif;

        if (isset($adata['card']['last4Digits'])):
?>
            <span class="details-label"><?= Security::htmlentities(_("last4Digits")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['card']['last4Digits']); ?></span><br>
<?php
        endif;

        if (isset($adata['card']['expiryMonth'])):
?>
            <span class="details-label"><?= Security::htmlentities(_("expiryMonth")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['card']['expiryMonth']); ?></span><br>
<?php
        endif;
        
        if (isset($adata['card']['expiryYear'])):
?>
            <span class="details-label"><?= Security::htmlentities(_("expiryYear")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['card']['expiryYear']); ?></span><br>
<?php
        endif;
    endif;
    
    if (isset($adata['customer'])):
?>
        <h4>
            <?= _("customer"); ?>
        </h4>
<?php
        if (isset($adata['customer']['email'])):
?>
            <span class="details-label"><?= Security::htmlentities(_("email")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['customer']['email']); ?></span><br>
<?php
        endif;

        if (isset($adata['customer']['id'])):
?>
            <span class="details-label"><?= Security::htmlentities(_("id")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['customer']['id']); ?></span><br>
<?php
        endif;
    endif;
