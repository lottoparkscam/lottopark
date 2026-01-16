<h3><?= _("Cubits"); ?></h3>
<?php 
    if (isset($adata['status'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['address'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Bitcoin Address")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['address']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['create_time'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Create Time")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['create_time']); ?></span><br>
<?php 
    endif;

    if (isset($adata['invoice_currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Invoice Currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['invoice_currency']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['invoice_amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Invoice Amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['invoice_amount']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['paid_currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Paid Currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['paid_currency']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['paid_amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Paid Amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['paid_amount']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['pending_currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Pending Currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['pending_currency']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['pending_amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Pending Amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['pending_amount']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['merchant_currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Merchant Currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['merchant_currency']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['merchant_amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Merchant Amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['merchant_amount']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['share_to_keep_in_btc'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Share to keep in BTC")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['share_to_keep_in_btc']); ?></span><br>
<?php 
    endif;
