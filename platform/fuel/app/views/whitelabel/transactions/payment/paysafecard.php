<h3>
    <?= _("paysafecard"); ?>
</h3>
<?php 
    if (isset($adata['object'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Object")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['object']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['created'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Created")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['created']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['updated'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Updated")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['updated']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['amount']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['currency']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['status'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['status_before_expiration'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status before expiration")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status_before_expiration']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['type'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Type")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['type']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']['id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Customer ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']['id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']['ip'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Customer IP")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']['ip']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['card_details'])):
?>
        <h4>
            <?= _("Card details"); ?>
        </h4>
<?php 
        foreach ($adata['card_details'] as $key => $details):
            if (isset($details['serial'])):
?>
                <span class="details-label"><?= Security::htmlentities(_("Serial")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($details['serial']); ?></span><br>
        <?php 
            endif;
            
            if (isset($details['type'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Type")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($details['type']); ?></span><br>
        <?php 
            endif;
            
            if (isset($details['country'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Country")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($details['country']); ?></span><br>
        <?php 
            endif;
            
            if (isset($details['currency'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Currency")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($details['currency']); ?></span><br>
        <?php 
            endif;
            
            if (isset($details['amount'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Amount")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($details['amount']); ?></span><br>
<?php 
            endif;
        endforeach;
    endif;
