<h3>
    <?= _("Skrill"); ?>
</h3>
<?php 
    if (isset($adata['pay_to_email'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Merchant e-mail")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['pay_to_email']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['pay_from_email'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("User e-mail")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['pay_from_email']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['mb_amount']) && isset($adata['mb_currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Skrill amount")); ?>:</span>
        <span class="details-value"><?= Lotto_View::format_currency($adata['mb_amount'], $adata['mb_currency'], true); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['failed_reason_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Failed reason code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['failed_reason_code']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Customer ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['merchant_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Merchant ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['merchant_id']); ?></span><br>
<?php 
    endif;
