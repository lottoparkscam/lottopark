<h3>
    <?= _("Sofort"); ?>
</h3>
<?php 
    if (isset($adata['amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['amount']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['amount_refunded'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Amount Refunded")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['amount_refunded']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['payment_method'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Payment Method")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['payment_method']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['consumer_protection'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Consumer Protection")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['consumer_protection']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['status'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['status_reason'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status Reason")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status_reason']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['status_modified_time'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status Modified Time")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status_modified_time']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['language_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Language Code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['language_code']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['currency']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['reason'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Reason")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['reason']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['test'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Test")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['test']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['time'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status Modified Time")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['time']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['project_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Project ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['project_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['recipient_holder'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Recipient Holder")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['recipient_holder']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['recipient_account_number'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Recipient Account Number")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['recipient_account_number']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['recipient_bank_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Recipient Bank Code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['recipient_bank_code']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['recipient_country_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Recipient Country Code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['recipient_country_code']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['recipient_bank_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Recipient Bank Name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['recipient_bank_name']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['recipient_bic'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Recipient BIC")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['recipient_bic']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['recipient_iban'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Recipient IBAN")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['recipient_iban']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['sender_holder'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Sender Holder")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['sender_holder']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['sender_account_number'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Sender Account Number")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['sender_account_number']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['sender_bank_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Sender Bank Code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['sender_bank_code']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['sender_country_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Sender Country Code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['sender_country_code']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['sender_bank_name'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Sender Bank Name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['sender_bank_name']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['sender_bic'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Sender BIC")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['sender_bic']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['sender_iban'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Sender IBAN")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['sender_iban']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['exchange_rate'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Exchange Rate")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['exchange_rate']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['costs_exchange_rate'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Costs (Exchange Rate)")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['costs_exchange_rate']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['costs_fees'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Costs (Fees)")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['costs_fees']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['status_history'])):
?>
        <h4>
            <?= _("Status History"); ?>
        </h4>
<?php 
        foreach ($adata['status_history'] as $key => $details):
            if (isset($details[0])):
?>
                    <span class="details-label"><?= Security::htmlentities(_("Status")); ?> [<?= $key + 1; ?>]:</span>
                    <span class="details-value"><?= Security::htmlentities($details[0]); ?></span><br>
            <?php 
                endif;
                
                if (isset($details[1])):
            ?>
                    <span class="details-label"><?= Security::htmlentities(_("Status Reason")); ?> [<?= $key + 1; ?>]:</span>
                    <span class="details-value"><?= Security::htmlentities($details[1]); ?></span><br>
            <?php 
                endif;
                
                if (isset($details[2])):
            ?>
                    <span class="details-label"><?= Security::htmlentities(_("Time")); ?> [<?= $key + 1; ?>]:</span>
                    <span class="details-value"><?= Security::htmlentities($details[2]); ?></span><br>
            <?php 
                endif;
        endforeach;
    endif;
