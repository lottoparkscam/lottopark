<h3>
    <?= _("ecoPayZ"); ?>
</h3>
<?php 
    if (isset($adata['status'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['status_description'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status description")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status_description']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_type'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Transaction type")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_type']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_customer_account'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Customer account")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_customer_account']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_processing_time'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Processing time")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_processing_time']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_result_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Result code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_result_code']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_result_description'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Result description")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_result_description']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_batch_number'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Batch number")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_batch_number']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_firstname'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Customer firstname")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_firstname']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_lastname'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Customer lastname")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_lastname']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_country'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Customer country")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_country']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_postal_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Customer Postal code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_postal_code']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer_ip'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Customer IP")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer_ip']); ?></span><br>
<?php 
    endif;
