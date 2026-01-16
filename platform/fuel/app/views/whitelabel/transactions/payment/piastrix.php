<h3>
    <?= _("Piastrix"); ?>
</h3>
<?php 
    if (isset($adata['client_price'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Client price")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['client_price']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['created'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Created")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['created']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['processed'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Processed")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['processed']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['description'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Description")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['description']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['payway'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Payway")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['payway']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['ps_currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Payment currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['ps_currency']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['shop_amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Shop amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['shop_amount']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['shop_currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Shop currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['shop_currency']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['shop_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Shop ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['shop_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['shop_invoice_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Shop invoice ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['shop_invoice_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['shop_refund'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Shop refund")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['shop_refund']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['status'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['test_add_on'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Test add on")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['test_add_on']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['addons'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Addons")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['addons']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['ps_data'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Payment data")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities(json_encode($adata['ps_data'])); ?></span><br>
<?php
    endif;
