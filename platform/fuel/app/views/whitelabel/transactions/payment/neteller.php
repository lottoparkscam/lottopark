<h3>
    <?= _("Neteller"); ?>
</h3>
<?php 
    if (isset($adata['order_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Order ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['order_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['event_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Event ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['event_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['event_date'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Event Date")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['event_date']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['event_type'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Event Type")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['event_type']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['attempt_number'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Attempt Number")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['attempt_number']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities(number_format(bcdiv($adata['amount'], "100", 4), 2, ".", "")); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['currency']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['create_date'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Create Date")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['create_date']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['update_date'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Update Date")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['update_date']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['error_code'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Error Code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['error_code']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['error_message'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Error Message")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['error_message']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['status'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['status']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['transaction_type'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Transaction Type")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['transaction_type']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['description'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Description")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['description']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->customerId) && isset($adata['customer']->customerId)):
?>
        <span class="details-label"><?= Security::htmlentities(_("Customer ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->customerId); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->email)):
?>
        <span class="details-label"><?= Security::htmlentities(_("E-mail")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->email); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->accountId)):
?>
        <span class="details-label"><?= Security::htmlentities(_("Account ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->accountId); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->firstName)):
?>
        <span class="details-label"><?= Security::htmlentities(_("First Name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->firstName); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->lastName)):
?>
        <span class="details-label"><?= Security::htmlentities(_("Last Name")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->lastName); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->country)):
?>
        <span class="details-label"><?= Security::htmlentities(_("Country")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->country); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->dateOfBirth)):
?>
        <span class="details-label"><?= Security::htmlentities(_("Date of Birth")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->dateOfBirth->year.'/'.$adata['customer']->accountProfile->dateOfBirth->month.'/'.$adata['customer']->accountProfile->dateOfBirth->day); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->address1)):
?>
        <span class="details-label"><?= Security::htmlentities(_("Address #1")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->address1); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->address2)):
?>
        <span class="details-label"><?= Security::htmlentities(_("Address #2")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->address2); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->address3)):
?>
        <span class="details-label"><?= Security::htmlentities(_("Address #3")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->address3); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->city)):
?>
        <span class="details-label"><?= Security::htmlentities(_("City")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->city); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->countrySubdivisionCode)):
?>
        <span class="details-label"><?= Security::htmlentities(_("Region")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->countrySubdivisionCode); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->postCode)):
?>
        <span class="details-label"><?= Security::htmlentities(_("Post Code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->postCode); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) && isset($adata['customer']->accountProfile->gender)):
?>
        <span class="details-label"><?= Security::htmlentities(_("Gender")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->gender); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) &&
        isset($adata['customer']->accountProfile->contactDetails) &&
        count($adata['customer']->accountProfile->contactDetails) > 0
    ):
?>
        <h4>
            <?= _("Customer Contact Details"); ?>
        </h4>
<?php 
        foreach ($adata['customer']->accountProfile->contactDetails as $key => $details):
            if (isset($details->type)):
?>
                <span class="details-label"><?= Security::htmlentities(_("Type")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($details->type); ?></span><br>
<?php 
            endif;

            if (isset($details->value)):
?>
                <span class="details-label"><?= Security::htmlentities(_("Value")); ?> [<?= $key + 1; ?>]:</span>
                <span class="details-value"><?= Security::htmlentities($details->value); ?></span><br>
<?php 
            endif;
        endforeach;
    endif;

    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) &&
        isset($adata['customer']->accountProfile->accountPreferences) &&
        isset($adata['customer']->accountProfile->accountPreferences->lang)
    ):
?>
        <span class="details-label"><?= Security::htmlentities(_("Language")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->accountPreferences->lang); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) && isset($adata['customer']->accountProfile) &&
        isset($adata['customer']->accountProfile->accountPreferences) &&
        isset($adata['customer']->accountProfile->accountPreferences->currency)
    ):
?>
        <span class="details-label"><?= Security::htmlentities(_("Currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->accountProfile->accountPreferences->currency); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) &&
        isset($adata['customer']->verificationLevel)
    ):
?>
        <span class="details-label"><?= Security::htmlentities(_("Verification Level")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['customer']->verificationLevel); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['customer']) &&
        isset($adata['customer']->availableBalance)
    ):
?>
        <span class="details-label">
            <?= Security::htmlentities(_("Customer Balance")); ?>:
        </span>
        <span class="details-value">
            <?php 
                $amount = number_format(bcdiv($adata['customer']->availableBalance->amount, "100", 4), 2, ".", "");
                $currency = $adata['customer']->availableBalance->currency;
                echo Security::htmlentities($amount . " " . $currency);
            ?>
        </span>
        <br>
<?php 
    endif;

    if (isset($adata['billing_detail'])):
?>
        <h4>
            <?= _("Billing Details"); ?>
        </h4>
        <?php 
            if (isset($adata['billing_detail']->email)):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("E-mail")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['billing_detail']->email); ?></span><br>
        <?php 
            endif;
            
            if (isset($adata['billing_detail']->firstName)):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("First Name")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['billing_detail']->firstName); ?></span><br>
        <?php 
            endif;
            
            if (isset($adata['billing_detail']->lastName)):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Last Name")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['billing_detail']->lastName); ?></span><br>
        <?php 
            endif;
            
            if (isset($adata['billing_detail']->country)):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Country")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['billing_detail']->country); ?></span><br>
        <?php 
            endif;
            
            if (isset($adata['billing_detail']->address1)):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Address #1")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['billing_detail']->address1); ?></span><br>
        <?php 
            endif;
            
            if (isset($adata['billing_detail']->address2)):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Address #2")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['billing_detail']->address2); ?></span><br>
        <?php 
            endif;
            
            if (isset($adata['billing_detail']->address3)):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Address #3")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['billing_detail']->address3); ?></span><br>
        <?php 
            endif;
            
            if (isset($adata['billing_detail']->city)):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("City")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['billing_detail']->city); ?></span><br>
        <?php 
            endif;
            
            if (isset($adata['billing_detail']->countrySubdivisionCode)):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Region")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['billing_detail']->countrySubdivisionCode); ?></span><br>
        <?php 
            endif;
            
            if (isset($adata['billing_detail']->postCode)):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Post Code")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['billing_detail']->postCode); ?></span><br>
        <?php 
            endif;
            
            if (isset($adata['billing_detail']->lang)):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Language")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['billing_detail']->lang); ?></span><br>
        <?php 
            endif;
            
    endif;
    
    if (isset($adata['fees'])):
?>
        <h4>
            <?= _("Fees"); ?>
        </h4>
        <?php 
            foreach ($adata['fees'] as $key => $details):
                if (isset($details->feeName)):
        ?>
                    <span class="details-label"><?= Security::htmlentities(_("Fee Name")); ?> [<?= $key + 1; ?>]:</span>
                    <span class="details-value"><?= Security::htmlentities($details->feeName); ?></span><br>
            <?php 
                endif;
                
                if (isset($details->feeType)):
            ?>
                    <span class="details-label"><?= Security::htmlentities(_("Fee Type")); ?> [<?= $key + 1; ?>]:</span>
                    <span class="details-value"><?= Security::htmlentities($details->feeType); ?></span><br>
            <?php 
                endif;
                
                if (isset($details->feeAmount)):
            ?>
                    <span class="details-label"><?= Security::htmlentities(_("Fee Amount")); ?> [<?= $key + 1; ?>]:</span>
                    <span class="details-value"><?= Security::htmlentities(number_format(bcdiv($details->feeAmount, "100", 4), 2, ".", "")); ?></span><br>
            <?php 
                endif;
                
                if (isset($details->feeCurrency)):
            ?>
                    <span class="details-label"><?= Security::htmlentities(_("Fee Currency")); ?> [<?= $key + 1; ?>]:</span>
                    <span class="details-value"><?= Security::htmlentities($details->feeCurrency); ?></span><br>
<?php 
                endif;
            endforeach;
    endif;
