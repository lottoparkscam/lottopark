<h3>
    <?= _("CoinPayments"); ?>
</h3>
<?php
    if (isset($adata['ipn_version'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("IPN Version")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['ipn_version']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['ipn_type'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("IPN Type")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['ipn_type']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['ipn_mode'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("IPN Mode")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['ipn_mode']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['ipn_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("IPN ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['ipn_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['merchant'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Merchant")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['merchant']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['first_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("First name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['first_name']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['last_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Last name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['last_name']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['company'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Company")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['company']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['email'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("E-mail")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['email']); ?></span><br>
<?php 
    endif;

    // Field address1/2 changed to address_1/2 but some old data can still have old version address_1

    if (isset($adata['address1']) || isset($adata['address_1'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Address #1")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['address1'] ?? $adata['address_1']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['address2']) || isset($adata['address_2'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Address #2")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['address2'] ?? $adata['address_2']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['city'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("City")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['city']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['state'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("State")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['state']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['zip'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("ZIP")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['zip']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['country'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Country")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['country']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['country_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Country name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['country_name']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['phone'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Phone")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['phone']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['send_tx'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Send TX")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['send_tx']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['received_amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Received amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['received_amount']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['received_confirms'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Received confirms")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['received_confirms']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['subtotal'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Subtotal")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['subtotal']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['fee'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Fee")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['fee']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['tax'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Tax")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['tax']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['shipping'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Shipping")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['shipping']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['net'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Net")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['net']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['item_amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Item amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['item_amount']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['item_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Item name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['item_name']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['amount1'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Amount #1")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['amount1']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['amount2'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Amount #2")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['amount2']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['currency1'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Currency #1")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['currency1']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['currency2'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Currency #2")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['currency2']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['status'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['status_text'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status text")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status_text']); ?></span><br>
<?php 
    endif;
